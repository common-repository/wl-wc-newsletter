<?php

global $wpdb;

$email = sanitize_text_field($_POST['billing_email']);
$billing_first_name = sanitize_text_field($_POST['billing_first_name']);
$billing_last_name = sanitize_text_field($_POST['billing_last_name']);
$now = date('Y-m-d H:i:s');
$now_str = "'".$now."'";
$is_customer = $now_str;

$user = null;
if($user_id)
{
    $user = wp_get_current_user();

    $f_name = $billing_first_name;
    $l_name = $billing_last_name;
    $is_member = $now_str;
}
else
{
    $user = get_user_by_email($email);
    if($user)
    {
        $name_arr = explode(' ', $user->display_name);
        $f_name = $name_arr[0];
        $l_name = '';
        if(count($name_arr) > 1)
        {
            unset($name_arr[0]);
            foreach($name_arr as $key => $val)
            {
                $l_name .= $val.' ';
            }

            $l_name = trim($l_name);
        }
        $is_member = $now_str;
    }
    else
    {
        $f_name = $billing_first_name;
        $l_name = $billing_last_name;
        $is_member = 'NULL';
    }
}

$plugin_prefix = wlwcn_getPluginTablePrefix();

$sql = "SELECT * FROM `".$wpdb->prefix.$plugin_prefix."email_addresses` WHERE `email`='".$email."';";
$email_obj = $wpdb->get_results($sql);

$new_subscription = isset($_POST['subscribe_newsletter']);
$deleted_at = $new_subscription ? 'NULL' : $now_str;

$sc = new SettingsController;
$coupon_enabled = $sc->isCouponEnabled();
$subscription_coupon = $coupon_id = $subscription_coupon_sent_at = 'NULL';

if(!empty($email_obj))
{
    $subscriber = $email_obj[0];

    if(!$new_subscription)
    {
        if($subscriber->subscription_coupon)
        {
            $coupon_post = [
                    'ID' => $subscriber->coupon_id,
                    'post_status' => 'draft'
                ];

            wp_update_post($coupon_post);
            $coupon_id = $subscriber->coupon_id;
            $subscription_coupon = "'".$subscriber->subscription_coupon."'";
            $subscription_coupon_sent_at = "'".$subscriber->subscription_coupon_sent_at."'";
        }
    }
    else if(!$subscriber->subscription_coupon)
    {
        if($coupon_enabled)
        {
            require_once 'coupon/create.php';
            require_once 'send_subscription_coupon.php';
            $subscription_coupon = "'".$coupon_code."'";
            $subscription_coupon_sent_at = $now_str;
        }
        else if($subscriber->deleted_at)
        {
            require_once 'send_subscription_welcome.php';
        }
    }
    else
    {
        if($coupon_enabled)
        {
            $coupon_id = $subscriber->coupon_id;
            $subscription_coupon = "'".$subscriber->subscription_coupon."'";
            $subscription_coupon_sent_at = "'".$subscriber->subscription_coupon_sent_at."'";

            $coupon_post = [
                    'ID' => $coupon_id,
                    'post_status' => 'publish'
                ];
            $coupon_post_id = wp_update_post($coupon_post);

            if($coupon_post_id)
            {
                $coupon_code = $subscriber->subscription_coupon;
                $coupon_obj = new WC_Coupon($coupon_code);

                if($coupon_obj->get_discount_type() == 'percent')
                {
                    $discount_label = $coupon_obj->get_amount()."%";
                }
                else
                {
                    $cur_symbol = get_woocommerce_currency_symbol();
                    $discount_label = $cur_symbol.$discount_amount;
                }

                require_once 'get_subscription_settings.php';
                $expiry_in = 0;
                if($coupon_obj->get_date_expires())
                {
                    $expiry_in = 1;
                    $expiry_date = $coupon_obj->get_date_expires()->format('Y-m-d');
                }
                require_once 'send_subscription_coupon.php';
            }
        }
        else if($subscriber->deleted_at)
        {
            require_once 'send_subscription_welcome.php';
        }
    }

    $subscribed_at = $subscriber->subscribed_at ? "'".$subscriber->subscribed_at."'" : 'NULL';
    if($new_subscription && ($subscriber->deleted_at || !$subscriber->subscribed_at) )
    {
        $subscribed_at = $now_str;
    }

    $sql = "UPDATE `".$wpdb->prefix.$plugin_prefix."email_addresses` SET
        `deleted_at`= ".$deleted_at.",
        `subscribed_at`= ".$subscribed_at.",
        `is_member`=".$is_member.",
        `is_customer`=".$is_customer.",
        `f_name`='".$f_name."',
        `l_name`='".$l_name."',
        `coupon_id`=".$coupon_id.",
        `subscription_coupon`=".$subscription_coupon.",
        `subscription_coupon_sent_at`=".$subscription_coupon_sent_at."
        WHERE `email`='".$email."'";
}
else
{
    $subscribed_at = 'NULL';
    if($new_subscription)
    {
        $subscribed_at = $now_str;
        if($coupon_enabled)
        {
            require_once 'coupon/create.php';
            require_once 'send_subscription_coupon.php';
            $subscription_coupon = "'".$coupon_code."'";
            $subscription_coupon_sent_at = $now_str;
        }
        else
        {
            require_once 'send_subscription_welcome.php';
        }
    }

    $sql = "INSERT INTO `".$wpdb->prefix.$plugin_prefix."email_addresses` SET
        `deleted_at`= ".$deleted_at.",
        `subscribed_at`= ".$subscribed_at.",
        `is_member`=".$is_member.",
        `is_customer`=".$is_customer.",
        `f_name`='".$f_name."',
        `l_name`='".$l_name."',
        `subscription_coupon`=".$subscription_coupon.",
        `coupon_id`=".$coupon_id.",
        `subscription_coupon_sent_at`=".$subscription_coupon_sent_at.",
        `email`='".$email."'";
}

$row = $wpdb->get_results($sql);
