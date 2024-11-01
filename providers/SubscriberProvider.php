<?php

namespace Providers;

use Eloquent\SubscriberEM;
use Eloquent\MailingListEM;

class SubscriberProvider
{
    protected $model;

    function __construct()
    {
        $this->model = new SubscriberEM;
    }

    function setMemberMailingList($id)
    {
        $model = $this->model;
        $model = $model->withTrashed()->where('id', $id)->first();
       
        $mailing_list = $model->mailingLists()->where('slug', 'member')->first();
        
        if(!$mailing_list)
        {
            $guest = MailingListEM::where('slug', 'guest')->first();
            if($guest)
            {
               $model->mailingLists()->detach($guest->id);
            }
            
            $member = MailingListEM::where('slug', 'member')->first();
            if($member)
            {
                $model->mailingLists()->attach($member->id); 
            }
        }
    }

    function setGuestMailingList($id)
    {
        $model = $this->model;
        $model = $model->withTrashed()->where('id', $id)->first();

        $mailing_list = $model->mailingLists()->where('slug', 'guest')->first();

        if(!$mailing_list)
        {
            $guest = MailingListEM::where('slug', 'guest')->first();
            if($guest)
            {
                $model->mailingLists()->attach($guest->id);
            }
        }
    }

    function setCustomerMailingList($email)
    {
        $model = $this->model;
        $model = $model->withTrashed()->where('email', $email)->first();
        if(!$model->is_customer)
        {
            $model->is_customer = date('Y-m-d H:i:s');
            $model->save();
        }
       
        $mailing_list = $model->mailingLists()->where('slug', 'customer')->first();

        if(!$mailing_list)
        {
            $guest = MailingListEM::where('slug', 'guest')->first();
            if($guest)
            {
                $model->mailingLists()->detach($guest->id);
            }
            
            $customer = MailingListEM::where('slug', 'customer')->first();
            if($customer)
            {
                $model->mailingLists()->attach($customer->id);
            }
        }
    }
}
