<?php

namespace Controllers;

use Models\Wlwcn_Settings;
use Validators\SettingsValidator;

class SettingsController
{
    protected $settings;

    function __construct()
    {
        $this->settings = new Wlwcn_Settings;
    }

    function getSettings()
    {
        return $this->settings->getSettings();
    }

    function getMetaSingle()
    {
        return $this->settings->getMetaSingle('subscription_details');
    }

    function isSubscriptionEnabled()
    {
        return $this->settings->getMetaSingle('enable_subscription');
    }

    function update($request)
    {
        $validator = new SettingsValidator;
        $validator->validateUpdate();
        $now = date('Y-m-d H:i:s');

        $request = wlwcn_trimRequest($request);

        $subscription_details = (isset($request['subscription_details']) && $request['subscription_details']) ? stripslashes($request['subscription_details']) : NULL;

        $data = [
                'enable_subscription' => isset($request['enable_subscription']) ? $now : NULL,
                'enable_subscription_offer' => isset($request['enable_subscription_offer']) ? $now : NULL,
                'mail_from_address' => sanitize_email($request['from_email']),
                'mail_replyto_address' => sanitize_email($request['replyto_email']),
                'subscription_details' => $subscription_details
            ];
        $settings = new Wlwcn_Settings;
        $this->settings->update($data);

        $_SESSION['flash']['alert_type'] = 'success';
        $_SESSION['flash']['alert_msg'] = 'Newsletter subscription settings successfully updated.';

        $redirect_to = 'admin.php?page='.WLWCN_SETTINGS_SLUG.'-settings';
        wp_redirect($redirect_to);
        exit;
    }

    function isCouponEnabled()
    {
        return $this->settings->getMetaSingle('enable_subscription_offer');
    }
}
