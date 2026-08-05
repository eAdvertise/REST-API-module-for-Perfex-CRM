<?php

//inject permissions Feature and Capabilities for webhooks module
hooks()->add_filter('staff_permissions', 'webhooks_module_permissions_for_staff');
function webhooks_module_permissions_for_staff($permissions)
{
    $viewGlobalName =
        _l('permission_view') . '(' . _l('permission_global') . ')';
    $allPermissionsArray = [
        'view'   => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    $permissions['WEBHOOKS'] = [
        'name'         => _l('webhooks'),
        'capabilities' => $allPermissionsArray,
    ];

    return $permissions;
}