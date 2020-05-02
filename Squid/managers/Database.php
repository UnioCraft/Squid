<?php

class Database
{
    /** @var $connection mysqli */
    private static $connection;

    private static function connect()
    {
        require(__ROOT__ . '/constants/MySQL.php');
        Database::$connection = new mysqli($host, $username, $password);

        if (Database::$connection->connect_error) {
            die("Bağlantı hatası. Lütfen bir yöneticiyle iletişime geçin.");
        }
    }

    public static function updateSQL($query)
    {
        Database::connect();
        if (Database::$connection->query($query)) {
            Database::$connection->close();
            return true;
        } else {
            echo("Bağlantı hatası. Lütfen bir yöneticiyle iletişime geçin.");
            Database::$connection->close();
            return false;
        }
    }

    public static function exist($uniqueValue, $dbName, $tableName, $uniqueColumn)
    {
        Database::connect();
        $query = "SELECT " . $uniqueColumn . " FROM `" . $dbName . "`.`" . $tableName . "` WHERE " . $uniqueColumn . " = '" . $uniqueValue . "';";

        if ($result = Database::$connection->query($query)) {
            if ($result->num_rows > 0) {
                Database::$connection->close();
                return true;
            }
        }

        Database::$connection->close();
        return false;
    }

    public static function get($userName, $dbName, $tableName, $columnName, $userNameColumn)
    {
        Database::connect();
        $query = "SELECT " . $columnName . " FROM `" . $dbName . "`.`" . $tableName . "` WHERE " . $userNameColumn . " = '" . $userName . "';";

        if ($result = Database::$connection->query($query)) {
            if ($result->num_rows > 0) {
                $row = $result->fetch_object();
                Database::$connection->close();
                return $row->$columnName;
            }
        }

        Database::$connection->close();
        return -1;
    }

    public static function set($userName, $dbName, $tableName, $columnName, $userNameColumn, $amount, $create)
    {
        if (Database::exist($userName, $dbName, $tableName, $userNameColumn)) {
            return Database::updateSQL("UPDATE `" . $dbName . "`.`" . $tableName .
                "` SET `" . $columnName . "` = '" . $amount .
                "' WHERE `" . $userNameColumn . "` = '" . $userName . "';");
        } else if ($create) {
            Database::create($userName, $dbName, $tableName, $columnName, $userNameColumn);
            return Database::set($userName, $dbName, $tableName, $columnName, $userNameColumn, $amount, false);
        }
        return false;
    }

    public static function add($userName, $dbName, $tableName, $columnName, $userNameColumn, $amount, $create)
    {
        if (Database::exist($userName, $dbName, $tableName, $userNameColumn)) {
            return Database::updateSQL("UPDATE `" . $dbName . "`.`" . $tableName .
                "` SET `" . $columnName . "` = '" . (Database::get($userName, $dbName, $tableName, $columnName, $userNameColumn) + $amount) .
                "' WHERE `" . $userNameColumn . "` = '" . $userName . "';");
        } else if ($create) {
            Database::create($userName, $dbName, $tableName, $columnName, $userNameColumn);
            return Database::add($userName, $dbName, $tableName, $columnName, $userNameColumn, $amount, false);
        }
        return false;
    }

    public static function remove($userName, $dbName, $tableName, $columnName, $userNameColumn, $amount, $create)
    {
        if (Database::exist($userName, $dbName, $tableName, $userNameColumn)) {
            return Database::updateSQL("UPDATE `" . $dbName . "`.`" . $tableName .
                "` SET `" . $columnName . "` = '" . (Database::get($userName, $dbName, $tableName, $columnName, $userNameColumn) - $amount) .
                "' WHERE `" . $userNameColumn . "` = '" . $userName . "';");
        } else if ($create) {
            Database::create($userName, $dbName, $tableName, $columnName, $userNameColumn);
            return Database::remove($userName, $dbName, $tableName, $columnName, $userNameColumn, $amount, false);
        }
        return false;
    }

    private static function create($userName, $dbName, $tableName, $columnName, $userNameColumn)
    {
        if (!Database::exist($userName, $dbName, $tableName, $userNameColumn)) {
            return Database::updateSQL("INSERT INTO `" . $dbName . "`.`" . $tableName .
                "` (" . $userNameColumn . ", " . $columnName . ") " .
                "VALUES ('" . $userName . "','" . 0 . "');");
        } else {
            return false;
        }
    }

    public static function getGeneralVIPExpiration($player, $vipType, $server)
    {
        Database::connect();
        $query = "SELECT bitistarihi FROM `genel`.`vip` WHERE player = '" . $player . "' AND vipturu = '" . $vipType . "' AND server = '" . strtolower($server) . "' AND bitistarihi >= CURDATE();";

        if ($result = Database::$connection->query($query)) {
            if ($result->num_rows > 0) {
                $row = $result->fetch_object();
                Database::$connection->close();
                return $row->bitistarihi;
            }
        }
        Database::$connection->close();
        return null;
    }

    public static function getXenForoVIPExpiration($userID)
    {
        Database::connect();
        $query = "SELECT end_date FROM `website`.`xf_user_upgrade_active` WHERE user_id = '" . $userID . "'";

        if ($result = Database::$connection->query($query)) {
            if ($result->num_rows > 0) {
                $row = $result->fetch_object();
                Database::$connection->close();
                return $row->end_date;
            }
        }

        Database::$connection->close();
        return null;
    }
}