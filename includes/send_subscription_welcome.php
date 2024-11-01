<?php

require_once 'get_subscription_settings.php';

$site_title = get_bloginfo('name');

$subject = "Thank you for subscribing to ".$site_title." newsletters.";
$message = "Hello ";
$message .= $f_name ? $f_name : 'there';
$message .= ", welcome to ".$site_title." newsletters. We thank you for subscribing to our newsletter service. By subscribing to our newsletter service you will ocassionally receive email regarding new & exciting offers & product launches. We promise you that we won't be spamming you.";

$message .= '<br><br>If you have any questions or concerns, you can always get in touch with us by replying to this email or by contacting us at: <a href="mailto:'.$replyto_email.'" target="_blank">'.$replyto_email.'</a> <br><br>Thank You, <br>'.$site_title.'.';

$headers = ['Content-Type: text/html; charset=UTF-8'];
$headers[] = 'From: '.$site_title.' <'.$from_email.'>';
$response = wp_mail($email, $subject, $message, $headers);

if(WP_DEBUG)
{
    $fn = ABSPATH . '/mail.log';
    $fp = fopen($fn, 'a');

    $log_msg = "\n\n\nNew Log @ ".date('Y-m-d H:i:s')." (TZ: ".date_default_timezone_get()."): Mail sent with response: ";
    $response_status = $response ? 'true' : 'false';
    $log_msg .= $response_status;
    $mail_msg = preg_replace('#<br\s*/?>#i', "\n", $message);
    $log_msg .= "\n\nMessage:\n\n".$mail_msg;

    fputs($fp, $log_msg);
    fclose($fp);
}
