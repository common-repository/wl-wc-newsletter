<?php

namespace Eloquent;

use WeDevs\ORM\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriberEM extends Model
{
    use SoftDeletes;

    protected $table = 'email_addresses';
    protected $dates = ['is_customer', 'is_member', 'reviewed_us_on', 'subscription_coupon_sent_at', 'subscribed_at', 'deleted_at'];
    protected $mwpdb;
    protected $table_prefix;
    protected $with_trashed = false;
    protected $only_trashed = false;

    function __construct()
    {
        global $wpdb;

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table_prefix = $mwpdb->prefix.wlwcn_getPluginTablePrefix();
        $this->table = $this->table_prefix.$this->table;
    }

    function withTrashed()
    {
        $this->with_trashed = true;

        return $this;
    }

    function onlyTrashed()
    {
        $this->only_trashed = true;

        return $this;
    }

    function newQuery()
    {
        $query = parent::newQuery();
        if($this->only_trashed)
        {
            $query->whereNotNull('deleted_at');
        }

        if(!$this->with_trashed)
        {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    function scopeReceivers($query)
    {
        if(empty($receiver_options))
        {
            return [];
        }

        foreach($receiver_options as $key => $val)
        {
            if($key == 'customers')
            {
                $this->orWhere(function($query) use ($val) {
                        $query->whereNotNull('is_customer');
                        if(isset($val['selected']))
                        {
                            $query->whereIn('id', $val['selected']);
                        }
                        else if(isset($val['except']))
                        {
                            $query->whereNotIn('id', $val['except']);
                        }
                    });
            }
            else if($key == 'members')
            {
                $this->orWhere(function($query) use ($val) {
                        $query->whereNotNull('is_member');
                        if(isset($val['selected']))
                        {
                            $query->whereIn('id', $val['selected']);
                        }
                        else if(isset($val['except']))
                        {
                            $query->whereNotIn('id', $val['except']);
                        }
                    });
            }
        }

        return $this;
    }

    function mailingLists()
    {
        return $this->belongsToMany('Eloquent\MailingListEM', $this->table_prefix.'email_address_mailing_list', 'email_address_id', 'mailing_list_id');
    }
}
