<?php

require_once __DIR__ . '/vendor/autoload.php';

use Controllers\SettingsController;
use Controllers\SubscriberController;
use Controllers\NewsletterController;
use Controllers\MailingListController;

function wlwcn_mailing_lists()
{
    $file = wlwcn_get_page_type('load_mailing_lists');

    require_once __DIR__.'/admin/'.$file.'.php';
}

function wlwcn_get_page_type($default='settings')
{
    $page = $default;
    if(isset($_GET['type']))
    {
        $type = sanitize_text_field($_GET['type']);

        $valid_types = ['subscriber', 'newsletter', 'mailing-list'];
        if(in_array($type, $valid_types))
        {
            $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : false;
            $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : false;

            if($type == 'subscriber')
            {
                if(wlwcn_isInteger($id) && ($id > 0))
                {
                    $page = 'edit_subscriber';
                }
            }
            else if($type == 'newsletter')
            {
                $page = 'create_newsletter';

                if(wlwcn_isInteger($id) && ($id > 0) && ($action == 'edit'))
                {
                    $page = 'edit_newsletter';
                }
                else if(wlwcn_isInteger($id) && ($id > 0) && ($action == 'duplicate'))
                {
                    $page = 'duplicate_newsletter';
                }
                else if(wlwcn_isInteger($id) && ($id > 0))
                {
                    $page = 'show_newsletter';
                }
            }
            else if($type == 'mailing-list')
            {
                $page = 'load_mailing_lists';

                if(wlwcn_isInteger($id) && ($id > 0) && ($action == 'edit'))
                {
                    $page = 'edit_mailing_list';
                }
                else if(wlwcn_isInteger($id) && ($id > 0))
                {
                    $page = 'show_mailing_list';
                }
            }
        }
    }

    return $page;
}

add_filter('cron_schedules', 'wlwcn_schedules');
function wlwcn_schedules($schedules)
{
    if(!isset($schedules['5min']))
    {
        $schedules['5min'] = [
                'interval' => 300,
                'display'  => __('Once every 5 minutes.')
            ];
    }

    return $schedules;
}

function wlwcn_customize_admin_title($admin_title, $title)
{
    $return = $admin_title;

    $page_type = wlwcn_get_page_type();

    $page_arr = [
            'edit_subscriber' => 'Edit &lsaquo; '.$admin_title,
            'create_newsletter' => 'Create &lsaquo; '.$admin_title,
            'edit_newsletter' => 'Edit &lsaquo; '.$admin_title,
            'show_newsletter' => 'Newsletter Details &lsaquo; '.$admin_title,
            'duplicate_newsletter' => 'Create Newsletter &lsaquo; '.$admin_title,
            'duplicate_mailing_list' => 'Create Mailing List &lsaquo; '.$admin_title
        ];

    if(isset($page_arr[$page_type]))
    {
        $return = $page_arr[$page_type].' &lsaquo; '.$admin_title;
    }

    return $return;
}
add_filter('admin_title', 'wlwcn_customize_admin_title', 10, 2);

function wlwcn_subscribers()
{
    $file = wlwcn_get_page_type('load_subscribers');

    require_once __DIR__.'/admin/'.$file.'.php';
}

function wlwcn_newsletters()
{
    $file = wlwcn_get_page_type('load_newsletters');
    require_once __DIR__.'/admin/'.$file.'.php';
}

function wlwcn_display_pagination($total_rows, $per_page, $cur_page, $current_rows='')
{
    $total_pages = $total_rows / $per_page;
    $tot_arr = explode('.', $total_pages);
    $total_pages = $tot_arr[0];
    if(isset($tot_arr[1]))
    {
        if($tot_arr[1] != 0)
        {
            $total_pages = $tot_arr[0] + 1;
        }
    }

    require_once __DIR__.'/views/admin/pagination.php';
}

function wlwcn_pluginBaseUrl($type='settings', $append=[])
{
    $url = "admin.php";
    $query_args = ['page' => WLWCN_SETTINGS_SLUG.'-'.$type];
    $query_args += $append;

    $i = 0;
    $count = count($query_args);
    foreach($query_args as $key => $val)
    {
        ++$i;
        if($i == 1)
        {
            $url .= '?';
        }

        $url .= $key.'='.urlencode($val);

        if($i < $count)
        {
            $url .= '&';
        }
    }

    return admin_url($url);
}

function wlwcn_add_settings_link($links=[])
{
    $settings_link = '<a href="admin.php?page='.WLWCN_SETTINGS_SLUG.'-settings">' . __( 'Settings' ) . '</a>';
    array_unshift($links, $settings_link);

  	return $links;
}
$plugin = plugin_basename(WLWCN_ROOT_FILE);
add_filter("plugin_action_links_$plugin", 'wlwcn_add_settings_link');

function wlwcn_register_plugin_links( $links, $file )
{
    if ( plugin_basename(WLWCN_ROOT_FILE) === $file)
    {
        $premium_link = '<a target="_blank" title="Try our Premium version for 7 days for Free & get 25% OFF" href="https://checkout.freemius.com/mode/dialog/plugin/11618/plan/19815/?trial=paid&coupon=free2premium">' . __( '<b>Upgrade to Premium **Try 7 days for FREE & get 25% OFF**</b>' ) . '</a>';
        $links[] = $premium_link;

        $support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/wl-wc-newsletter/">' . __( 'Support Forum' ) . '</a>';
        $links[] = $support_link;

    }

    return $links;
}
add_filter('plugin_row_meta', 'wlwcn_register_plugin_links', 10, 2 );

function wlwcn_clear_old_inputs()
{
    if(isset($_SESSION['old']))
    {
        unset($_SESSION['old']);
    }
}

function wlwcn_clear_flash_session()
{
    if(isset($_SESSION['flash']))
    {
        unset($_SESSION['flash']);
    }

    wlwcn_clear_old_inputs();
}
add_filter('admin_print_footer_scripts', 'wlwcn_clear_flash_session');

function wlwcn_update_mailing_list()
{
    $ctrl = new MailingListController;
	$ctrl->update($_REQUEST);
}
add_action('admin_post_wlwcn_update_mailing_list', 'wlwcn_update_mailing_list');

function wlwcn_update_newsletter()
{
    require_once __DIR__.'/controllers/NewsletterController.php';
	$nc = new NewsletterController;
	$nc->update($_REQUEST);
}
add_action('admin_post_wlwcn_update_newsletter', 'wlwcn_update_newsletter');

function wlwcn_store_newsletter()
{
    require_once __DIR__.'/controllers/NewsletterController.php';
	$nc = new NewsletterController;
	$nc->store($_REQUEST);
}
add_action('admin_post_wlwcn_store_newsletter', 'wlwcn_store_newsletter');

function wlwcn_update_subscriber()
{
    require_once __DIR__.'/controllers/SubscriberController.php';
	$sc = new SubscriberController;
	$sc->update($_REQUEST);
}
add_action('admin_post_wlwcn_update_subscriber', 'wlwcn_update_subscriber');

function wlwcn_delete_subscriber()
{
    require_once __DIR__.'/controllers/SubscriberController.php';
	$sc = new SubscriberController;
	$sc->delete($_REQUEST);
}
add_action('admin_post_wlwcn_delete_subscriber', 'wlwcn_delete_subscriber');

function wlwcn_delete_newsletter()
{
    require_once __DIR__.'/controllers/NewsletterController.php';
	$nc = new NewsletterController;
	$nc->delete($_REQUEST);
}
add_action('admin_post_wlwcn_delete_newsletter', 'wlwcn_delete_newsletter');

function wlwcn_update_settings()
{
    wlwcn_set_old_inputs();
	$sc = new SettingsController;
	$sc->update($_REQUEST);
}
add_action('admin_post_wlwcn_update_settings', 'wlwcn_update_settings');

function wlwcn_register_settings_menu()
{
    /*
    add_menu_page(
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        callable $callback = '',
        string $icon_url = '',
        int|float $position = null
    )
    */
    $position = WP_DEBUG ? 6 : 50;
    add_menu_page(
        WLWCN_NAME.' Settings',
        WLWCN_NAME_ABBR,
        'manage_options',
        WLWCN_SETTINGS_SLUG.'-settings',
        'wlwcn_settings',
        plugin_dir_url(__FILE__).'assets/images/web-logix-logo-short-3-32p.png',
        $position
    );

    /*
    add_submenu_page(
        string $parent_slug,
        string $page_title,
        string $menu_title,
        string $capability,
        string $menu_slug,
        callable $callback = '',
        int|float $position = null
    )
    */
    add_submenu_page(
        WLWCN_SETTINGS_SLUG.'-settings',
        'Settings - '.WLWCN_NAME,
        'Settings',
        'manage_options',
        WLWCN_SETTINGS_SLUG.'-settings',
        'wlwcn_settings'
    );

    add_submenu_page(
        WLWCN_SETTINGS_SLUG.'-settings',
        'Subscribers - '.WLWCN_NAME,
        'Subscribers',
        'manage_options',
        WLWCN_SETTINGS_SLUG.'-subscriber',
        'wlwcn_subscribers'
    );

    add_submenu_page(
        WLWCN_SETTINGS_SLUG.'-settings',
        'Newsletters - '.WLWCN_NAME,
        'Newsletters',
        'manage_options',
        WLWCN_SETTINGS_SLUG.'-newsletter',
        'wlwcn_newsletters'
    );

    add_submenu_page(
        WLWCN_SETTINGS_SLUG.'-settings',
        'Mailing Lists | '.WLWCN_NAME,
        'Mailing Lists',
        'manage_options',
        WLWCN_SETTINGS_SLUG.'-mailing-list',
        'wlwcn_mailing_lists'
    );
}
add_action('admin_menu', 'wlwcn_register_settings_menu');

function wlwcn_settings()
{
    require_once __DIR__.'/admin/load_settings.php';
}

function wlwcn_load_admin_assets()
{
    wp_register_style('wlwcn-alertify', plugin_dir_url(WLWCN_ROOT_FILE).'assets/alertify/css/alertify.min.css');
    wp_register_style('wlwcn-alertify-theme', plugin_dir_url(WLWCN_ROOT_FILE).'assets/alertify/css/themes/default.min.css');

	wp_enqueue_style('wlwcn-alertify');
	wp_enqueue_style('wlwcn-alertify-theme');

    wp_register_script('wlwcn-alertify', plugin_dir_url(WLWCN_ROOT_FILE).'assets/alertify/alertify.min.js', ['jquery'], false, true);

    $script_after = ['jquery', 'wlwcn-alertify'];
    $form_pages = ['show_newsletter', 'create_newsletter', 'edit_newsletter', 'duplicate_newsletter', 'edit_subscriber', 'settings', 'edit_mailing_list'];

    $page_type = wlwcn_get_page_type();

    if(in_array($page_type, $form_pages) )
    {
        wp_register_style('wlwcn-bs-grid', plugin_dir_url(WLWCN_ROOT_FILE).'assets/bootstrap-4.6.2/bootstrap-grid.css');
        wp_register_style('wlwcn-select2', plugin_dir_url(WLWCN_ROOT_FILE).'assets/select2-4.1.0/select2.min.css');

        wp_enqueue_style('wlwcn-bs-grid');
        wp_enqueue_style('wlwcn-select2');

        wp_register_script('wlwcn-select2', plugin_dir_url(WLWCN_ROOT_FILE).'assets/select2-4.1.0/select2.min.js', ['jquery'], false, true);

        wp_enqueue_script('wlwcn-select2');

        $script_after += ['wlwcn-select2'];
    }

    wp_register_script('wlwcn', plugin_dir_url(WLWCN_ROOT_FILE).'assets/scripts.js', $script_after, false, true);

    wp_enqueue_script('wlwcn-alertify');
    wp_enqueue_script('wlwcn');
}

function wlwcn_load_newsletter_assets($hook)
{
    wlwcn_load_styles();
    wlwcn_load_admin_assets();
}
add_action('admin_enqueue_scripts', 'wlwcn_load_newsletter_assets');

function wlwcn_activated()
{
	require_once __DIR__.'/db/migrate.php';

    if(!wp_next_scheduled('wlwcn_cron_hook'))
    {
        wp_schedule_event(time(), '5min', 'wlwcn_cron_hook');
    }
}
register_activation_hook(WLWCN_ROOT_FILE, 'wlwcn_activated');

function wlwcn_newsletter_subs_uninstall()
{
	require_once __DIR__.'/db/rollback.php';
}
register_uninstall_hook(WLWCN_ROOT_FILE, 'wlwcn_newsletter_subs_uninstall');
