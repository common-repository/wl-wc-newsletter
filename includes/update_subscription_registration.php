<?php

global $wpdb;

$email = sanitize_text_field($_POST['email']);
$now = date('Y-m-d H:i:s');
$now_str = "'".$now."'";
$is_member = $now_str;

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
    if($subscriber->subscription_coupon)
    {
        $coupon_post = [
                'ID' => $subscriber->coupon_id,
                'post_status' => $new_subscription ? 'publish' : 'draft'
            ];

        $coupon_post_id = wp_update_post($coupon_post);

        if($coupon_enabled && $coupon_post_id && $subscriber->deleted_at && $new_subscription)
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
        else if($new_subscription && $subscriber->deleted_at)
        {
            require_once 'send_subscription_welcome.php';
        }

        $coupon_id = $subscriber->coupon_id;
        $subscription_coupon = "'".$subscriber->subscription_coupon."'";
        $subscription_coupon_sent_at = "'".$subscriber->subscription_coupon_sent_at."'";
    }
    else if($new_subscription)
    {
        if($coupon_enabled)
        {
            require_once 'coupon/create.php';
            require_once 'send_subscription_coupon.php';

            $subscription_coupon = "'".$coupon_code."'";
            $subscription_coupon_sent_at = $now_str;
        }
        else if ($subscriber->deleted_at)
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
        `coupon_id`=".$coupon_id.",
        `subscription_coupon`=".$subscription_coupon.",
        `subscription_coupon_sent_at`=".$subscription_coupon_sent_at.",
        `is_member`=".$is_member."
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
        `is_member`=".$is_member.",
        `deleted_at`= ".$deleted_at.",
        `subscribed_at`= ".$subscribed_at.",
        `coupon_id`=".$coupon_id.",
        `subscription_coupon`=".$subscription_coupon.",
        `subscription_coupon_sent_at`=".$subscription_coupon_sent_at.",
        `email`='".$email."'";
}


$row = $wpdb->get_results($sql);
