<?php

namespace Validators;

class SettingsValidator extends Validator
{
    function __construct()
    {
        parent::__construct();
    }

    function validateUpdate()
    {
        $request = $_REQUEST;
        $rules = [
                'enable_subscription' => 'required_when:enable_subscription_offer',
                'replyto_email' => 'required|email|max:190',
                'from_email' => 'required|email|max:190',
                'subscription_details' => 'max:9999'
            ];

        $this->validate($rules, $request);
    }
}
