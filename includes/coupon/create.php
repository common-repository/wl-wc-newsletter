<?php

require_once __DIR__.'/../get_subscription_settings.php';

$amount_suffix = str_ireplace('.', '', $discount_amount);
$email_arr = explode('@', $email);
$mt = microtime();
$mt_suffix = substr($mt, 6, 2);
$date_suffix = date('nj');
$coupon_code = $email_arr[0].'-'.$amount_suffix.$date_suffix.$mt_suffix;

$coupon = new WC_Coupon();

$coupon->set_code($coupon_code);

if($discount_type == 'percent')
{
    $discount_label = $discount_amount."%";
}
else
{
    $discount_type = 'fixed_cart';
    $cur_symbol = get_woocommerce_currency_symbol();
    $discount_label = $cur_symbol.$discount_amount;
}

// discount type can be 'fixed_cart', 'percent' or 'fixed_product', defaults to 'fixed_cart'
$coupon->set_discount_type($discount_type);

$description = $discount_label." OFF to ".$email." for newsletter subscription.";

$coupon->set_description($description);

// General tab

// discount amount
$coupon->set_amount($discount_amount);

// allow free shipping
$coupon->set_free_shipping(false);

if($expiry_in && ($expiry_in > 0))
{
    $date = new DateTime;
    $expiry_date = $date->modify('+'.$expiry_in.' day')->format('Y-m-d');

    // coupon expiry date
    $coupon->set_date_expires($expiry_date);
}

// Usage Restriction

// individual use only
$coupon->set_individual_use( true );

// exclude sale items
$coupon->set_exclude_sale_items( true );

// allowed emails
$coupon->set_email_restrictions([$email]);


// Usage limit tab

// usage limit per coupon
$coupon->set_usage_limit(1);

// usage limit per user
$coupon->set_usage_limit_per_user(1);

$coupon->save();
$coupon_id = $coupon->id;
