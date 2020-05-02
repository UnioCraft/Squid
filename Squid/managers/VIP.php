<?php

class VIP
{

    public static $currentDate;
    public static $expirationDate;
    public static $currentTime;
    public static $expirationTime;

    public static function giveVIP($buyer, $receiver, $viptype, $server)
    {
        require(__ROOT__ . '/constants/Constants.php');
        require_once('Log.php');

        VIP::initDate($receiver, $viptype, $server);
        VIP::giveVIPGeneral($receiver, $viptype, $server);

        if ($permPlugins[$server] == "LuckPerms") {
            VIP::giveVIPLuckPerms($receiver, $viptype, $server);
        } else if ($permPlugins[$server] == "zPermissions") {
            VIP::giveVIPzPermissions($receiver, $viptype, $server);
        }
        echo "1";
        VIP::giveVIPLuckPerms($receiver, $viptype, "Lobi");
        VIP::giveVIPXenForo($receiver);
        VIP::giveVIPRewards($receiver, $viptype, $server);
        Log::logVIP($buyer, $receiver, VIP::$currentDate, VIP::$expirationDate, $viptype, $server);
        VIP::alertVIP($buyer, $receiver, $viptype, $server);
    }

    private static function giveVIPGeneral($receiver, $viptype, $server)
    {
        require_once('Database.php');
        Database::updateSQL("INSERT INTO `genel`.`vip` (id, player, vipturu, bitistarihi, server)
							VALUES (NULL, '$receiver', '$viptype', '" . VIP::$expirationDate . "', '" . strtolower($server) . "')
							ON DUPLICATE KEY UPDATE bitistarihi='" . VIP::$expirationDate . "';");
    }

    private static function giveVIPLuckPerms($receiver, $viptype, $server)
    {
        require_once('RemoteConnection.php');
        require(__ROOT__ . '/constants/Constants.php');
        if (!RemoteConnection::sendCommand($server, "lp user " . $receiver . " parent addtemp " . $viptype . " " . $vipDuration . "d")) {
            VIP::holdVIP($receiver, $viptype, $server);
        }
    }

    private static function giveVIPzPermissions($receiver, $viptype, $server)
    {
        require_once('RemoteConnection.php');
        require(__ROOT__ . '/constants/Constants.php');
        if (!RemoteConnection::sendCommand($server, "perm player " . $receiver . " addgroup -a " . $viptype . " " . $vipDuration . "d")) {
            VIP::holdVIP($receiver, $viptype, $server);
        }
    }

    private static function giveVIPXenForo($receiver)
    {
        require(__ROOT__ . '/constants/Constants.php');
        require_once('Database.php');

        $extraContent = 'a:4:{s:11:"cost_amount";s:4:"5.00";s:13:"cost_currency";s:3:"usd";s:13:"length_amount";i:1;s:11:"length_unit";s:5:"month";}';

        $userID = VIP::getUserID($receiver);
        if ($userID == -1) return false;

        $currentExpiration = Database::getXenForoVIPExpiration($userID);
        if (!is_null($currentExpiration)) {
            $expirationTime = strtotime('+' . $vipDuration . ' day', $currentExpiration);
            Database::updateSQL("UPDATE `website`.`xf_user_upgrade_active` SET end_date='$expirationTime' WHERE user_id = '$userID';");
            return true;
        }

        Database::updateSQL("UPDATE `website`.`xf_user` SET secondary_group_ids='5' WHERE username = '$receiver';");
        Database::updateSQL("INSERT INTO `website`.`xf_user_upgrade_active` (user_upgrade_record_id, user_id, user_upgrade_id, extra, start_date, end_date) 
            VALUES (NULL, '" . $userID . "', '1', '$extraContent', '" . VIP::$currentTime . "', '" . VIP::$expirationTime . "');");
        Database::updateSQL("INSERT INTO `website`.`xf_user_group_change` (user_id, change_key, group_ids) 
			VALUES ('" . $userID . "', 'userUpgrade-1', '5');");
        Database::updateSQL("UPDATE `website`.`xf_user` SET display_style_group_id='5' WHERE username = '$receiver';");
        Database::updateSQL("UPDATE `website`.`xf_user` SET permission_combination_id='11' WHERE username = '$receiver';");
        return true;
    }

    /** @noinspection SqlResolve */
    private static function giveVIPRewards($receiver, $viptype, $server)
    {
        require(__ROOT__ . '/constants/Constants.php');
        require_once('RemoteConnection.php');
        require_once('Database.php');

        RemoteConnection::sendCommand("Lobi", "cevher ver " . $receiver . " 10000");
        RemoteConnection::sendCommand("Lobi", "givemysteryboxtoplayer " . $receiver . " vip");

        $current = Database::get($receiver, $serverTables[$server], "vipodul", $viptype, "isim");
        if ($current == -1) {
            Database::updateSQL("INSERT INTO `" . $serverTables[$server] . "`.`vipodul` (`id`, `isim`, `" . $viptype . "`) 
								VALUES (NULL, '$receiver', '1');");
        } else {
            Database::updateSQL("UPDATE `" . $serverTables[$server] . "`.`vipodul` SET `$viptype` = '" . ($current + 1) . "' WHERE `vipodul`.`isim` = '$receiver'");
        }
    }

    private static function alertVIP($buyer, $receiver, $viptype, $server)
    {
        require_once('RemoteConnection.php');
        RemoteConnection::sendDiscordCommand($receiver . " AnnounceVIP " . $viptype);
        if ($buyer != $receiver) {
            RemoteConnection::sendCommand("BungeeCord", "alert &b" . $buyer . "&a, &b" . $receiver . "&a isimli oyuncuya, &b" . $server . " &asunucusundan &b" . $viptype . " &asatın aldı!");
        } else {
            RemoteConnection::sendCommand("BungeeCord", "alert &b" . $receiver . "&a, &b" . $server . " &asunucusundan &b" . $viptype . " &asatın aldı!");
        }
    }

    private static function initDate($receiver, $viptype, $server)
    {
        require(__ROOT__ . '/constants/Constants.php');
        require_once('Database.php');
        date_default_timezone_set('Europe/Moscow');
        VIP::$currentDate = (new DateTime())->format("Y-m-d H:i:s");
        VIP::$expirationDate = (new DateTime())->add(DateInterval::createFromDateString($vipDuration . " days"))->format("Y-m-d H:i:s");

        VIP::$currentTime = time();
        VIP::$expirationTime = time() + $vipDuration * 86400;

        $existExpiration = Database::getGeneralVIPExpiration($receiver, $viptype, $server);
        if (!is_null($existExpiration)) {
            VIP::$expirationDate = DateTime::createFromFormat("Y-m-d H:i:s", $existExpiration)->add(DateInterval::createFromDateString($vipDuration . " days"))->format("Y-m-d H:i:s");
            VIP::$expirationTime = strtotime('+' . $vipDuration . ' day', strtotime(VIP::$expirationDate));
        }
    }

    private static function holdVIP($receiver, $viptype, $server)
    {
        require_once('Database.php');
        Database::updateSQL("INSERT INTO `genel`.`vipOnHold` (id, player, vipturu, bitistarihi, server)
							VALUES (NULL, '$receiver', '$viptype', '" . VIP::$expirationDate . "', '" . strtolower($server) . "')
							ON DUPLICATE KEY UPDATE bitistarihi='" . VIP::$expirationDate . "';");
    }

    private static function getUserID($player)
    {
        require_once('Database.php');
        return Database::get($player, "website", "xf_user", "user_id", "username");
    }
}