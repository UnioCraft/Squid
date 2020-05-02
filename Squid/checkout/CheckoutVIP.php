<?php

class CheckoutVIP
{
    public function __construct($buyer, $receiver, $viptype, $server)
    {
        require(__ROOT__ . '/constants/Constants.php');
        require_once(__ROOT__ . '/managers/VIP.php');
        require_once(__ROOT__ . '/managers/Credit.php');

        if (!in_array($buyer, $adminNames)) {

            $vipPriceFormat = $server . ":" . $viptype;

            if (!Credit::check($buyer, $vipPrices[$vipPriceFormat])) {
                return print(6);
            }

            if (!Credit::take($buyer, $vipPrices[$vipPriceFormat])) {
                return print(7);
            }
        }

        VIP::giveVIP($buyer, $receiver, $viptype, $server);
    }
}