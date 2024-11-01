<?php

use Models\Wlwcn_Model;
use Models\Wlwcn_Subscriber;
use Exception;
use PDOException;
use PDO;

global $wpdb, $user_ID;

$model = new Wlwcn_Model;
$plugin_prefix = $model->prefix;
$table_prefix = $wpdb->prefix.$plugin_prefix;

if(!isset($log_msg))
{
    $log_msg = '';
}

$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->beginTransaction();

try {
    $sql = "ALTER TABLE `".$table_prefix."email_messages` DROP IF EXISTS `sent_from`;";
    $stmt = $db->exec($sql);
} catch (PDOException $e) {
    $db->rollBack();
    $log_msg .= "SQL error executing: \n".$sql;
}

$sql = "ALTER TABLE `".$table_prefix."email_address_message`
MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;";
$result = $wpdb->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".$table_prefix."mailing_lists` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `title` VARCHAR(190) NULL DEFAULT NULL , `slug` VARCHAR(255) NULL DEFAULT NULL, `description` TEXT NULL DEFAULT NULL , `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , `updated_at` TIMESTAMP NULL DEFAULT NULL , PRIMARY KEY (`id`), UNIQUE (`title`)) ENGINE = InnoDB;";
$result = $wpdb->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".$table_prefix."email_address_mailing_list` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `email_address_id` INT UNSIGNED NULL DEFAULT NULL , `mailing_list_id` INT UNSIGNED NULL DEFAULT NULL , `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
$result = $wpdb->query($sql);

$default_mailing_lists = [
        'customer' => [
                'title' => 'Customer',
                'description' => 'Subscribers who have successfully made at least one order from your website. A customer can also be a member.'
            ],
        'member' => [
                'title' => 'Member',
                'description' => 'Subscribers who have signed up and created an account on the website. A member can also be a customer.'
            ],
        'guest' => [
                'title' => 'Guest',
                'description' => 'Any subscriber who is neither a customer nor a member is kept in this list.'
            ]
        ];

$inserted_mailing_lists = [];
foreach($default_mailing_lists as $key => $row)
{
    $model = new Wlwcn_Model($plugin_prefix.'mailing_lists');
    $extraction_m = $model;
    try {
        $em = $extraction_m->where(['title'=>$row['title']])->withTrashed()->firstOrFail();
        $inserted_mailing_lists[$key] = $em->id;
    } catch (Exception $e) {
        $data = [
                'slug' => $key,
                'title' => $row['title'],
                'description' => $row['description']
            ];

        $inserted_mailing_lists[$key] = $model->store($data);
    }
}

$subscriber = new Wlwcn_Subscriber;
$all = $subscriber->withTrashed()->get();
$eamls = [];
foreach($all as $key => $row)
{
    if($row->is_customer)
    {
        $eamls[] = [
                'mailing_list_id' => $inserted_mailing_lists['customer'],
                'email_address_id' => $row->id
            ];
    }

    if($row->is_member)
    {
        $eamls[] = [
                'mailing_list_id' => $inserted_mailing_lists['member'],
                'email_address_id' => $row->id
            ];
    }

    if(!$row->is_customer && !$row->is_member)
    {
        $eamls[] = [
                'mailing_list_id' => $inserted_mailing_lists['guest'],
                'email_address_id' => $row->id
            ];
    }
}

if(!empty($eamls))
{
    foreach($eamls as $key => $row)
    {
        $where = $row;
        $model = new Wlwcn_Model($plugin_prefix.'email_address_mailing_list');
        $extraction_m = $model;

        try {
            $extraction_m->where($where)->withTrashed()->firstOrFail();
        } catch (Exception $e) {
            $model->store($row);
        }
    }
}

$db->beginTransaction();
$sql = "ALTER TABLE `".$table_prefix."email_addresses` ADD  `notes` LONGTEXT NULL DEFAULT NULL AFTER `subscription_coupon_sent_at`; ";
try {
    $stmt = $db->exec($sql);
    $db->commit();

    $db = null;
} catch (PDOException $e) {
    $db->rollBack();
    $log_msg .= "SQL error executing: \n".$sql;
}
