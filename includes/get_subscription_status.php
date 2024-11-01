<?php

global $wpdb, $current_user;

$plugin_prefix = wlwcn_getPluginTablePrefix();

$email = $current_user->user_email;
$sql = "SELECT * FROM `".$wpdb->prefix.$plugin_prefix."email_addresses` WHERE `email`='".$email."';";
$results = $wpdb->get_results($sql);
