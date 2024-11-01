<?php

namespace Wlwcn_Eloquent;

use WeDevs\ORM\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriberEM extends Model
{
    use SoftDeletes;

    protected $table = 'email_addresses';
    protected $dates = ['is_customer', 'is_member', 'reviewed_us_on', 'subscription_coupon_sent_at', 'subscribed_at'];
    protected $plugin_prefix;

    function __construct()
    {
        global $wpdb;

        $this->plugin_prefix = wlwcn_getPluginTablePrefix();

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table = $mwpdb->prefix.$this->plugin_prefix.$this->table;
    }

    function scopeReceivers($query)
    {
        if(empty($receiver_options))
        {
            return [];
        }

        $customer = $member = $this;

        $customers = $customer->whereNotNull('is_customer')->get();
        $members = $member->whereNotNull('is_member')->get();

        $receivers = $where_in = $where_not_in = $or_where_not_null = [];

        $model = $this;

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
}
