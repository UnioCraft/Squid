<?php

class Utils
{
    public static function clearString($string)
    {
        $string = trim($string);
        $string = strip_tags($string);
        $string = addslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }

    public static function validateInteger($string)
    {
        if (filter_var($string, FILTER_VALIDATE_INT) === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function validateBoolean($string)
    {
        if (filter_var($string, FILTER_VALIDATE_BOOLEAN) === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function validateUsername($string)
    {
        $regex = '/[a-zA-Z0-9_]{1,16}/';
        if (preg_match($regex, $string) === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function validateServer($server, $vipType)
    {
        require(__ROOT__ . '/constants/Constants.php');
        return array_key_exists($server . ":" . $vipType, $vipPrices);
    }

    public static function getUsername()
    {
        require_once(__ROOT__ . '/utils/XenForoSDK.php');
        $sdk = new XenForoSDK;
        $visitor = $sdk->getVisitor();
        return !empty($visitor['username']) && !is_null($visitor['username']) ? $visitor['username'] : null;
    }
}