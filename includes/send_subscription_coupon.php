<?php

$site_title = get_bloginfo('name');

$subject = "Thank you for subscribing, here's a one time discount coupon for you.";
$message = '<div class="msg-body" style="font-size:15px;">Hello ';
$message .= $f_name ? $f_name : 'there';
$message .= ", welcome to ".$site_title." newsletters. We thank you for subscribing to our newsletter service. We will ocassionally send you email regarding new & exciting offers & product launches. We promise we won't be spamming you. Here's your one time coupon code that you can use to get <b>".$discount_label." OFF</b> on your next order.<br><br>Coupon Code: <b>".$coupon_code."</b> <br>Please use this coupon code on the cart page or checkout page to avail the discount.";
$message .= "<br><br><b> Coupon terms & conditions:</b><ol>";
$message .= "<li>This coupon is only valid for one time use.</li>";
if($expiry_in && ($expiry_in > 0))
{
    $message .= "<li>This coupon is only valid until ".$expiry_date.".</li>";
}
$message .= "<li>This coupon is only valid for the email address ".$email.".</li>";
$message .= "<li>".$site_title." holds the right to discard any coupon at it's sole discretion any time.</li>";
$message .= "</ol>";
$message .= '<br>If you have any questions or concerns, you can always get in touch with us by replying to this email or by contacting us at: <a href="mailto:'.$replyto_email.'" target="_blank">'.$replyto_email.'</a> <br><br>Thank You, <br>'.$site_title.'.</div>';

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
