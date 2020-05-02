<?php

class Log
{

    public static function logVIP($buyer, $receiver, $buyDate, $expirationDate, $vipType, $server)
    {
        require_once('Database.php');
        require(__ROOT__ . '/constants/Constants.php');

        $vipPriceFormat = $server . ":" . $vipType;

        Database::updateSQL("INSERT INTO `genel`.`vipZaman` (id, alistarihi, bitistarihi, sure, isim, vipturu, fiyat, server, ip, alici) 
							VALUES (NULL, '$buyDate', '$expirationDate', '$vipDuration', '$receiver', '$vipType', '" . $vipPrices[$vipPriceFormat] . "', '$serverIDs[$server]', '" . $_SERVER['REMOTE_ADDR'] . "', '$buyer');");
    }

}