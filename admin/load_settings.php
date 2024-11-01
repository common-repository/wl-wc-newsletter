<?php

use Controllers\SettingsController;

$sc = new SettingsController;
$settings = $sc->getSettings();

$settings_meta = get_post_meta($settings->ID, '', true);

$enable_subscription = $settings_meta['enable_subscription'][0];
$enable_subscription_offer = $settings_meta['enable_subscription_offer'][0];
$discount_type = $settings_meta['discount_type'][0];
$discount_amount = $settings_meta['discount_amount'][0];
$expiry_in = $settings_meta['expiry_in_days'][0];
$from_email = $settings_meta['mail_from_address'][0];
$replyto_email = $settings_meta['mail_replyto_address'][0];
$subscription_details = $settings_meta['subscription_details'][0];

require_once __DIR__.'/../views/admin/settings.php';
