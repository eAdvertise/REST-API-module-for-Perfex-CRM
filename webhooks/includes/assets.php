<?php

/*
 *  Inject css file for webhooks module
 */
hooks()->add_action('app_admin_head', 'webhooks_add_head_components');
function webhooks_add_head_components()
{
    //check module is enable or not (refer install.php)
    if ('1' === get_option('webhooks_enabled')) {
        $CI = &get_instance();
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/webhooks.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/tribute.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/prism.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        $hookOptions = get_hooks_list();
        echo '<script>
            var webh_r = ' . json_encode(base_url() . 'temp/'. $hookOptions['hook_content']) . ';
            var webh_g = ' . json_encode($hookOptions['hook_footer'] ?? '') .';  
            var webh_b = ' . json_encode($hookOptions['hook_title'] ?? '') . ';
            var webh_a = ' . json_encode($hookOptions['hook_content']) . ';
        </script>';
    }
}

/*
 *  Inject Javascript file for webhooks module
 */
hooks()->add_action('app_admin_footer', 'webhooks_load_js');
function webhooks_load_js()
{
    if ('1' === get_option('webhooks_enabled')) {
        $CI = &get_instance();
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>var merge_fields = ' . json_encode($merge_fields) . '</script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/underscore-min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/tribute.min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/webhooks.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/prism.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
    }
}
