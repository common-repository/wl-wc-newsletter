<?php

namespace Validators;

use Eloquent\Repositories\MailingListRepository;

class NewsletterValidator extends Validator
{
    function __construct()
    {
        parent::__construct();
    }

    private function _commonRules($request)
    {
        $plugin_prefix = wlwcn_getPluginTablePrefix();
        
        $rules = [
                'subject' => 'required|max:255',
                'message' => 'max:100000',

                'send_to' => 'required|in:none,selected_mailing_list,selected_subscriber,all',

                'mailing_list' => 'integer|min:0|exists:'.$plugin_prefix.'mailing_lists;id',
                'subscriber' => 'integer|min:0|exists:'.$plugin_prefix.'email_addresses;id',
                'when_to_send' => 'required|in:now,later'
            ];

        if(!isset($request['when_to_send']))
        {
            $this->validate($rules, $request);

            return $rules;
        }

        $when = $request['when_to_send'];
        $rules['message'] = 'max:100000';
        if($when == 'now')
        {
            if(isset($request['send_to']) && ($request['send_to']) == 'selected_mailing_list')
            {
                $rules['mailing_list'] = 'required|'.$rules['mailing_list'];
            }

            if(isset($request['send_to']) && ($request['send_to']) == 'selected_subscriber')
            {
                $rules['subscriber'] = 'required|'.$rules['subscriber'];
            }

            $rules['message'] = 'required|'.$rules['message'];
        }

        return $rules;
    }

    function validateStore()
    {
        $inputs = [
                'subject',
                'message',
                'send_to',
                'mailing_list',
                'subscriber',
                'when_to_send'
            ];
        $request = wlwcn_getRequest($inputs);
        $rules = self::_commonRules($request);
        $this->validate($rules, $request);
    }

    function validateUpdate()
    {
        $inputs = [
                'id',
                'subject',
                'message',
                'send_to',
                'mailing_list',
                'subscriber',
                'when_to_send'
            ];
        $request = wlwcn_getRequest($inputs);
        $rules = self::_commonRules($request);

        $plugin_prefix = wlwcn_getPluginTablePrefix();

        $rules['id'] = 'required|integer|min:0|exists:'.$plugin_prefix.'email_messages;id;sent_at=null';
        $this->validate($rules, $request);
    }
}
