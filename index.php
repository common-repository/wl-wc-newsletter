<?php
/**
 * Plugin Name: WL Newsletter for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/wl-wc-newsletter/
 * Text Domain: wl-wc-newsletter
 * Description: A WooCommerce based plugin to send discount coupon to users who opt in to subscribe to newsletter subscription while placing order or while registering or from my-account page. You can send or schedule personalized newsletter emails to be sent in bulk. Explore more features by going through the plugin menus after activating the plugin. You can also try our <strong>PREMIUM</strong> version with additional features for <strong>FREE for 7 days</strong> and get <strong>25% OFF</strong>.
 * Version: 1.1.1
 * Author: Web Logix
 * Author URI: https://profiles.wordpress.org/logixweb/
 */

function wlwcn_init_session()
{
    if (!session_id())
    {
        session_start();
    }

    if(isset($_POST) && !empty($_POST))
    {
        wlwcn_set_old_inputs();
    }
}
// Start session on init hook.
add_action('init', 'wlwcn_init_session');

require_once __DIR__.'/controllers/SettingsController.php';

if(!function_exists('get_plugin_data'))
{
    require_once(ABSPATH.'wp-admin/includes/plugin.php');
}

$plugin_info = get_plugin_data(__FILE__ );
$plugin_name = $plugin_info['Name'];
define('WLWCN_NAME', $plugin_name);
$nspn_arr = explode('for', $plugin_name);
define('WLWCN_NAME_ABBR', trim($nspn_arr[0]));
define('WLWCN_SETTINGS_SLUG', 'wlwcn');
define('WLWCN_ROOT_FILE', __FILE__);

require_once __DIR__.'/helper_functions.php';
require_once __DIR__.'/common_functions.php';
require_once __DIR__.'/admin_functions.php';

function wlwcn_upgrade_to_1_1($upgrader_object, $options)
{
    wlwcn_log('in upgrade function');
    $current_plugin_path_name = plugin_basename(__FILE__);
    if(($options['action'] == 'update') && ($options['type'] == 'plugin') )
    {
        wlwcn_log('in first if');
        $i = 0;
        foreach($options['plugins'] as $each_plugin)
        {
            wlwcn_log('in foreach with i'.$i);
            if ($each_plugin == $current_plugin_path_name)
            {
                wlwcn_log('in foreach if');
                require_once __DIR__.'/db/upgrade_to_1_1.php';
            }
            else
            {
                wlwcn_log('in foreach else');
            }
            ++$i;
        }
    }
    else
    {
        wlwcn_log('in first else');
    }
}
add_action('upgrader_process_complete', 'wlwcn_upgrade_to_1_1', 10, 2);
