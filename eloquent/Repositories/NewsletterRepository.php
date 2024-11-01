<?php

namespace Eloquent\Repositories;

use Eloquent\MailingListEM;
use Eloquent\SubscriberEM;
use Eloquent\NewsletterEM;

class NewsletterRepository extends NewsletterEM
{
    protected $mailinglist, $subscriber;

    function __construct()
    {
        $this->mailinglist = new MailingListEM;
        $this->subscriber = new SubscriberEM;
    }

    function getReceiversFromOptions($receiver_options=[])
    {
        $return = [];

        if(isset($receiver_options[0]) && ($receiver_options[0] == 'all'))
        {
            $return = $receiver_options;
        }
        else if(isset($receiver_options['selected_mailing_list']))
        {
            $ml_id = $receiver_options['selected_mailing_list'];
            $return = $this->mailinglist->withCount('subscribers')->where('id', $ml_id)->first();
        }
        else if(isset($receiver_options['selected_subscriber']))
        {
            $id = $receiver_options['selected_subscriber'];
            $return = $this->subscriber->where('id', $id)->first();
        }

        return $return;
    }
}
