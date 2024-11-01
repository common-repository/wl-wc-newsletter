<?php

namespace Mail;

use Exception;

class NewsletterMail
{
    protected $settings;
    function __construct()
    {

    }

    function send($subject, $message, $to_email, $mail_from, $mail_replyto)
    {
        $subject = wp_unslash($subject);
        $sitename = get_bloginfo('name');

        $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: '.$sitename.' <'.$mail_from.'>',
                'Reply-To: '.$sitename.' <'.$mail_replyto.'>'
            ];

        $response = wp_mail($to_email, $subject, $message, $headers);
        
        if(!$response)
        {
            if(is_array($to_email))
            {
                $to_email = json_encode($to_email);
            }
            throw new Exception("Mail couldn't be sent to: ".$to_email, 1);
        }
    }

}
