<?php

/*
 * Return Codes:
 * 1: Everything went accordingly.
 * 2: No POST value.
 * 3: Required spaces are not filled.
 * 4: Undefined action
 * 5: Agreement was not accepted.
 * 6: Not enough credit.
 * 7: Not enough credit or connection error.
 * 8: Couldn't pass input validation. (hatalı giriş yaptınız)
 * 9: Receiver does not exist.
 */

/*
 * Actions:
 * 1: Buy VIP
 * 2: Buy Custom Item
 * 3: Change Skin
 */

define('__ROOT__', dirname(__FILE__));
define('__XENFORO_ROOT__', dirname(dirname(dirname(__FILE__))));

// Imports
require_once('checkout/CheckoutVIP.php');
require_once('utils/Utils.php');
require_once('managers/Database.php');

$adminkey = "adminkey";

if (!$_POST) {
    print(2);
    header("Location: https://www.uniocraft.com/");
    return;
}

if (!isset($_POST['action']) || !isset($_POST['agreement']) || !isset($_POST['receiver'])) {
    return print(3);
}

$action = Utils::validateInteger(Utils::clearString($_POST['action'])) ? Utils::clearString($_POST['action']) : null;
$agreement = Utils::validateBoolean(Utils::clearString($_POST['agreement'])) ? Utils::clearString($_POST['agreement']) : null;
$buyer = Utils::getUsername();
$receiver = Utils::validateUsername(Utils::clearString($_POST['receiver'])) ? Utils::clearString($_POST['receiver']) : null;

if (isset($_POST['adminkey']) && isset($_POST['buyer'])) {
    if ($_POST['adminkey'] === $adminkey) {
        $buyer = Utils::validateUsername(Utils::clearString($_POST['buyer'])) ? Utils::clearString($_POST['buyer']) : null;
    }
}

if (is_null($action) || is_null($buyer) || is_null($receiver) || is_null($agreement)) {
    return print(8);
}

if (!$agreement) {
    return print(5);
}

if (!Database::exist($receiver, "website", "xf_user", "username")) {
    return print(9);
}

if ($action == 1) {
    if (!isset($_POST['server']) || !isset($_POST['viptype'])) {
        return print(3);
    }

    if (Utils::validateServer(Utils::clearString($_POST['server']), Utils::clearString($_POST['viptype']))) {
        $server = Utils::clearString($_POST['server']);
        $viptype = Utils::clearString($_POST['viptype']);
    } else {
        return print(8);
    }

    new CheckoutVIP($buyer, $receiver, $viptype, $server);
} else if ($action == 2) {
    // Custom Item
} else if ($action == 3) {
    // Change skin
} else {
    return print(4);
}

/*
 * TODO
 * Custom item action
 * Change skin action
 * viponhold cron job (if sending command not worked, add this to database. A cron job will try to give VIP every 10 minutes.)
 */