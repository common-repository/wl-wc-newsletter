<?php

use Controllers\SettingsController;
use Providers\SubscriberProvider;

function wlwcn_2way_encrypt($string="", $encrypt_decrypt='e')
{
    // Set default output value
    $output = null;
    // Set secret keys
    $secret_key = 'lf!^gf8g^3*s'; // Change this!
    $secret_iv = 'k&#|&9,dh4%:@'; // Change this!
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    // Check whether encryption or decryption
    if($encrypt_decrypt == 'e')
    {
        // We are encrypting
        $output = base64_encode(openssl_encrypt($string, "AES-256-CBC", $key, 0, $iv));
    }
    else if($encrypt_decrypt == 'd')
    {
        // We are decrypting
        $output = openssl_decrypt(base64_decode($string), "AES-256-CBC", $key, 0, $iv);
    }

    // Return the final value
    return $output;
}

function wlwcn_integrate_mail_template($message, $to_email)
{
    $template = @file_get_contents(__DIR__.'/views/email/template.php');

    $blog_info = get_bloginfo();
    $template = preg_replace('/\[SITE NAME\]/', $blog_info, $template);

    $site_url = get_site_url();
    $template = preg_replace('/\[SITE URL\]/', $site_url, $template);

    $date_y = date('Y');
    $template = preg_replace('/\[DATE Y\]/', $date_y, $template);

    $encrypted_email = wlwcn_2way_encrypt($to_email);

    $unsubscribe_url = add_query_arg([
            'action' => 'unsubscribe',
            'token' => $encrypted_email,
            ], home_url(WLWCN_SETTINGS_SLUG)
        );

    $template = preg_replace('/\[UNSUBSCRIBE URL\]/', $unsubscribe_url, $template);

    $footer_content = '';
    $template = preg_replace('/\[FOOTER CONTENT CONTAINER\]/', $footer_content, $template);

    $message = preg_replace('/\[MSG BODY\]/', $message, $template, 1);

    return $message;
}

function wlwcn_is_subscription_enabled()
{
    $sc = new SettingsController;
    return $sc->isSubscriptionEnabled();
}

function wlwcn_str_limit($str, $limit)
{
    $len = strlen($str);
    if($len > $limit)
    {
        $str = substr($str, 0, $limit).'...';
    }

    return $str;
}

function wlwcn_load_styles()
{
    wp_register_style('wlwcn-style', plugin_dir_url(WLWCN_ROOT_FILE).'assets/style.css');
    wp_register_style('wlwcn-responsive', plugin_dir_url(WLWCN_ROOT_FILE).'assets/responsive.css');
	wp_enqueue_style('wlwcn-style');
	wp_enqueue_style('wlwcn-responsive');
}

function wlwcn_display_ns_option_checkout($checkout)
{
    if(!wlwcn_is_subscription_enabled())
    {
        return;
    }
	$checked = wlwcn_checkPostedCheckbox('subscribe_newsletter', 'billing_email');
    $wcc = new WC_Checkout;
	if ( ! is_user_logged_in() && $wcc->is_registration_enabled() )
	{
        $sc = new SettingsController;
        $subs_details = $sc->getMetaSingle('subscription_details');
        $fg_class = 'mt--10';

        wlwcn_load_styles();
		require_once __DIR__.'/views/subscription_option.php';
	}
}
add_action('woocommerce_review_order_before_submit', 'wlwcn_display_ns_option_checkout');

function wlwcn_display_ns_option_registration()
{
    if(!wlwcn_is_subscription_enabled())
    {
        return;
    }

	$checked = wlwcn_checkPostedCheckbox('subscribe_newsletter', 'email');

    $sc = new SettingsController;
    $subs_details = $sc->getMetaSingle('subscription_details');
    $fg_class = 'mt--10';

    wlwcn_load_styles();
	require_once __DIR__.'/views/subscription_option.php';
}
add_action('woocommerce_register_form', 'wlwcn_display_ns_option_registration');

function wlwcn_getEmailSubscription()
{
	require_once __DIR__.'/includes/get_subscription_status.php';
	$email_obj = !empty($results) ? $results[0] : null;
	return	$email_obj ? !$email_obj->deleted_at : false;
}

function wlwcn_display_ns_option_account()
{
    if(!wlwcn_is_subscription_enabled())
    {
        return;
    }

    $sc = new SettingsController;
    $subs_details = $sc->getMetaSingle('subscription_details');

	$is_subscribed = wlwcn_getEmailSubscription();
	$checked = wlwcn_checkPostedCheckbox('subscribe_newsletter', 'account_first_name', $is_subscribed);

    wlwcn_load_styles();
	require_once __DIR__.'/views/subscription_option.php';
}
add_action('woocommerce_edit_account_form', 'wlwcn_display_ns_option_account');

function wlwcn_ns_at_checkout($user_id)
{
    if(!wlwcn_is_subscription_enabled())
    {
        return;
    }

	if(!isset($_POST['createaccount']) || !$_POST['createaccount'])
	{
		// begin subscription process at checkout when customer chooses not to create an account
		$is_customer = date('Y-m-d H:i:s');
		require_once __DIR__.'/includes/update_subscription_checkout.php';
	}
}
add_action('woocommerce_checkout_update_user_meta', 'wlwcn_ns_at_checkout');

function wlwcn_ns_at_registration($user_id)
{
    if(!wlwcn_is_subscription_enabled())
    {
        return;
    }

	if(isset($_POST['createaccount']))
	{
		// begin subscription process at checkout when customer chooses to create an account
		$is_customer = date('Y-m-d H:i:s');
		require_once __DIR__.'/includes/update_subscription_checkout.php';
	}
	else
	{
		// begin subscription process at registration
		require_once __DIR__.'/includes/update_subscription_registration.php';
	}
}
add_action('woocommerce_created_customer', 'wlwcn_ns_at_registration');

function wlwcn_update_subscription_account()
{
    if(!wlwcn_is_subscription_enabled())
    {
        return;
    }

	require_once __DIR__.'/includes/update_subscription_account.php';
}
add_action('woocommerce_save_account_details', 'wlwcn_update_subscription_account');

function wlwcn_update_subscriber_role_to_customer($order_id, $posted_data)
{
    $email = $posted_data['billing_email'];
    $sp = new SubscriberProvider;
    $sp->setCustomerMailingList($email);
}
add_action('woocommerce_checkout_order_processed', 'wlwcn_update_subscriber_role_to_customer', 10, 2);
