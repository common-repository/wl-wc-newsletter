<?php

global $wpdb;

$plugin_prefix = wlwcn_getPluginTablePrefix();

$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$plugin_prefix."email_address_mailing_list;";
$wpdb->query($sql);

$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$plugin_prefix."mailing_lists;";
$wpdb->query($sql);

$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$plugin_prefix."email_address_message;";
$wpdb->query($sql);

$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$plugin_prefix."email_addresses;";
$wpdb->query($sql);

$sql = "DROP TABLE IF EXISTS ".$wpdb->prefix.$plugin_prefix."email_messages;";
$wpdb->query($sql);

$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'wl-wc-newsletter'";
$result = $wpdb->get_results($sql);

if(!empty($result))
{
    foreach($result as $key => $row)
    {
        $id = $row->ID;

        $sql = "DELETE FROM ".$wpdb->prefix."postmeta WHERE post_id = ".$id;
        $wpdb->get_results($sql);
    }

    $sql = "DELETE FROM ".$wpdb->prefix."posts WHERE post_type = 'wl-wc-newsletter'";
    $wpdb->get_results($sql);
}
