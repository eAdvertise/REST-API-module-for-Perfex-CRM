<?php
defined('BASEPATH') or exit('No direct script access allowed');

$permissions['contactsplus'] = [
  'name' => _l('module_contactsplus'),
  'capabilities' => [
      'contactsplus_manage' => _l('contactsplus_perm_manage'),
  ]
];
