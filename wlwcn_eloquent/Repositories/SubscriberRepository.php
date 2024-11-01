<?php

namespace Wlwcn_Eloquent\Repositories;

use Wlwcn_Eloquent\SubscriberEM;

class SubscriberRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new SubscriberEM;
    }

    public function getReceiversFromOptions($receiver_options)
    {
        if(empty($receiver_options))
        {
            return [];
        }

        $model = $this->model;

        if(isset($receiver_option['subscribers']))
        {
            $subscriber = $receiver_option['subscribers'];
            if($subscriber != 'all')
            {
                $model = $model->where('id', $subscriber);
            }
        }

        return $model->get();
    }

    public function getReceiverFromOption($receiver_option)
    {
        if(empty($receiver_option))
        {
            return NULL;
        }

        $model = $this->model;

        if(isset($receiver_option['subscribers']))
        {
            $subscriber = $receiver_option['subscribers'];
            if($subscriber != 'all')
            {
                $model = $model->where('id', $subscriber);
            }
            else
            {
                return 'all';
            }
        }

        return $model->get();
    }
}
