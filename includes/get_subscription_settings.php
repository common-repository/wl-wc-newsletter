<?php

$nss = get_posts([
            'post_type' => 'wl-wc-newsletter',
        ]);

if(!empty($nss))
{
    $ns = $nss[0];

    /*
    get_post_meta( int $post_id, string $key = '', bool $single = false )
    */
    $discount_type = get_post_meta($ns->ID, 'discount_type', true);
    $discount_amount = get_post_meta($ns->ID, 'discount_amount', true);
    $expiry_in = get_post_meta($ns->ID, 'expiry_in_days', true);
    $from_email = get_post_meta($ns->ID, 'mail_from_address', true);
    $replyto_email = get_post_meta($ns->ID, 'mail_replyto_address', true);
}
