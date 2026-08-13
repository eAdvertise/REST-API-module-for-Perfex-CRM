<?php

// Inject sidebar menu and links for webhooks module
hooks()->add_action('admin_init', function () {
    $CI = &get_instance();
    if (staff_can('view', 'webhooks')) {
        $CI->app_menu->add_sidebar_menu_item('webhooks', [
            'slug' => 'webhooks',
            'name' => _l('webhooks'),
            'icon' => 'fa fa-handshake-o menu-icon fa-duotone fa-circle-nodes',
            'href' => 'webhooks',
            'position' => 30,
        ]);
    }

    if (staff_can('view', 'webhooks')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhooks',
            'name' => _l('webhooks'),
            'icon' => 'fa fa-compress',
            'href' => admin_url(WEBHOOKS_MODULE),
            'position' => 1,
        ]);
    }

    if (staff_can('view', 'webhooks')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhook_log',
            'name' => _l('webhook_log'),
            'icon' => 'fa fa-history',
            'href' => admin_url(WEBHOOKS_MODULE . '/logs'),
            'position' => 2,
        ]);
    }

    if (staff_can('view', 'webhooks')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhooks_cron',
            'name' => _l('webhooks_cron'),
            'icon' => 'fa fa-fan',
            'href' => admin_url('settings?group=webhooks'),
            'position' => 3,
        ]);
    }


    if (staff_can('view', 'webhooks')) {
        $CI->app_menu->add_sidebar_children_item('webhooks', [
            'slug' => 'webhooks_documentation',
            'name' => 'Documentation',
            'icon' => 'fa fa-book',
            'href' => module_dir_url(WEBHOOKS_MODULE, 'documentation/index.html'),
            'position' => 100,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ]);
    }

    if (WEB_CTL_PERFEX_VERSION) {
        get_instance()->app->add_settings_section_child('other', 'webhooks', [
            'name' => _l('webhooks_cron_job'),
            'view' => 'webhooks/settings/webhooks_cron_job',
            'position' => 1,
        ]);
    } else {
        get_instance()->app_tabs->add_settings_tab('webhooks', [
            'name' => _l('webhooks_cron_job'),
            'view' => 'webhooks/settings/webhooks_cron_job',
            'position' => 50,
        ]);
    }
});
