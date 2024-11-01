<?php

use Eloquent\MailingListEM;
use Models\Wlwcn_Model;

global $wpdb, $user_ID;

$model = new Wlwcn_Model;
$table_prefix = $wpdb->prefix.$model->prefix;

$sql = "CREATE TABLE IF NOT EXISTS `".$table_prefix."email_addresses` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `f_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `l_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `is_internal` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '0=>No; 1=>Yes;',
  `is_customer` timestamp NULL DEFAULT NULL,
  `is_member` timestamp NULL DEFAULT NULL,
  `sourced_from` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT '1=>Website; 2=>Others;',
  `reviewed_us_on` timestamp NULL DEFAULT NULL,
  `coupon_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `subscription_coupon` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `subscription_coupon_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subscribed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";
$result = $wpdb->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".$table_prefix."email_messages` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) DEFAULT NULL,
  `message` longtext,
  `sent_to` varchar(190) DEFAULT NULL,
  `receiver_options` longtext,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `timezone_offset` int NULL DEFAULT NULL COMMENT 'PHP based timezone offset in minutes.',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$result = $wpdb->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".$table_prefix."email_address_message` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscriber_id` int UNSIGNED DEFAULT NULL,
  `message_id` int UNSIGNED DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$result = $wpdb->query($sql);

$now = date('Y-m-d H:i:s');

$log_msg = "\n== $now : WL Newsletter for WooCommerce Activation Log ==";

$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'wl-wc-newsletter' LIMIT 1";
$result2 = $wpdb->get_results($sql);
$log_msg .= "\n".$sql;

$domain = $_SERVER['HTTP_HOST'];
if(substr($domain, 0, 4) == 'www.')
{
    $domain = substr($domain, 4);
}
$meta_arr = [
        'discount_type' => 'percent',
        'discount_amount' => '5',
        'expiry_in_days' => '30',
        'mail_from_address' => 'noreply@'.$domain,
        'mail_replyto_address' => 'info@'.$domain,
        'enable_subscription' => 1,
        'enable_subscription_offer' => 1
    ];

$meta_arr['subscription_details'] = "If you subscribe, you'll receive a one time <b>discount coupon</b> at your email address that you can use for your next order. We will also inform you when there are offers & discounts available in our website. We may also inform you about new & exciting product launches. You can always unsubscribe later if you want.";

if(empty($result2))
{
    $log_msg .= "\n result2 is empty";
    $new_post = [
            'post_title' => 'WL Newsletter for WooCommerce Settings',
            'post_type' => 'wl-wc-newsletter',
            'post_name' => 'wl-wc-newsletter',
            'post_content' => 'Manage newsletter subscription options & settings for WL Newsletter for WooCommerce',
            'post_status' => 'publish',
            'post_date' => $now,
            'post_author' => $user_ID,
            'post_category' => [0]
        ];
        $post_id = wp_insert_post($new_post, false, false);
        $log_msg .= "\n inserted post id: ".$post_id;

        /*
        add_post_meta( int $post_id, string $meta_key, mixed $meta_value, bool $unique = false )
        */
        $meta_ids = [];
        foreach($meta_arr as $key => $val)
        {
            $meta_id = add_post_meta($post_id, $key, $val, true);
            $meta_ids[] = $meta_id;
            $log_msg .= "\n inserted meta_id: ".$meta_id;
        }
}
else
{
    $log_msg .= "\n result2 is not empty";
    $post = $result2[0];
    $post_id = $post->ID;

    $sql = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE post_id = ".$post_id;
    $log_msg .= "\n".$sql;
    $post_metas = $wpdb->get_results($sql);

    if(empty($post_metas))
    {
        $log_msg .= "\n post_metas is empty";
        foreach($meta_arr as $key => $val)
        {
            $meta_id = add_post_meta($post_id, $key, $val, true);
            $meta_ids[] = $meta_id;
            $log_msg .= "\n inserted meta_id: ".$meta_id;
        }
    }
    else if(count($post_metas) < count($meta_arr))
    {
        $log_msg .= "\n post_metas is not empty";
        $meta_keys = array_keys($meta_arr);
        $exists = [];
        foreach ($post_metas as $meta)
        {
            $exists[] = $meta->meta_key;
        }

        foreach($meta_keys as $val)
        {
            if(!in_array($val, $exists))
            {
                $log_msg .= "\n val: ".$val." doesn't exist";
                $meta_id = add_post_meta($post_id, $val, $meta_arr[$val], true);
                $meta_ids[] = $meta_id;
                $log_msg .= "\n inserted meta_id: ".$meta_id;
            }
            else
            {
                $log_msg .= "\n ".$val." exists.";
            }
        }
    }
}

require_once 'upgrade_to_1_1.php';

wlwcn_log($log_msg, true);
