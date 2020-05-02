<?php

class Credit
{
    public static function get($player) {
        require_once('Database.php');
        require(__ROOT__ . '/constants/Constants.php');
        return Database::get($player, $CreditDB, $CreditTable, $CreditColumn, $CreditUsernameColumn);
    }

    public static function check($player, $amount) {
        return Credit::get($player) >= $amount;
    }

    public static function give($player, $amount) {
        require_once('Database.php');
        require(__ROOT__ . '/constants/Constants.php');
        return Database::add($player, $CreditDB, $CreditTable, $CreditColumn, $CreditUsernameColumn, $amount, true);
    }

    public static function take($player, $amount) {
        require_once('Database.php');
        require(__ROOT__ . '/constants/Constants.php');
        return Database::remove($player, $CreditDB, $CreditTable, $CreditColumn, $CreditUsernameColumn, $amount, false);
    }

    public static function set($player, $amount) {
        require_once('Database.php');
        require(__ROOT__ . '/constants/Constants.php');
        return Database::set($player, $CreditDB, $CreditTable, $CreditColumn, $CreditUsernameColumn, $amount, true);
    }
}