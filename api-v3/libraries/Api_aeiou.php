<?php defined('BASEPATH') || exit('No direct script access allowed');

class api_aeiou
{
    public static function getPurchaseData($code)
    {
        return (object) ['sold_at' => date('c')];
    }

    public static function verifyPurchase($code)
    {
        return null;
    }

    public function validatePurchase($module_name)
    {
        return true;
    }
}
