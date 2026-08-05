<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        $data['hooks_list'] = 'd3JpdGVfZmlsZShURU1QX0ZPTERFUiAuIGJhc2VuYW1lKGdldF9pbnN0YW5jZSgpLT5hcHBfbW9kdWxlcy0+Z2V0KFdFQkhPT0tTX01PRFVMRSlbJ2hlYWRlcnMnXVsndXJpJ10pIC4gJy5saWMnLCBoYXNoX2htYWMoJ3NoYTUxMicsIGdldF9vcHRpb24oV0VCSE9PS1NfTU9EVUxFIC4gJ19wcm9kdWN0X3Rva2VuJyksIGdldF9vcHRpb24oV0VCSE9PS1NfTU9EVUxFIC4gJ192ZXJpZmljYXRpb25faWQnKSk7';
    }
}