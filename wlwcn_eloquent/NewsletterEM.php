<?php

namespace Wlwcn_Eloquent;

use WeDevs\ORM\Eloquent\Model;

class NewsletterEM extends Model
{
    protected $table = 'email_messages';
    protected $plugin_prefix;
    protected $mwpdb;
    protected $casts = [
            'receiver_options' => 'array',
        ];
    protected $dates = ['sent_at', 'scheduled_at'];


    function __construct()
    {
        global $wpdb;

        $this->mwpdb = $mwpdb = $wpdb;
        $this->plugin_prefix = wlwcn_getPluginTablePrefix();
        $this->table = $mwpdb->prefix.$this->plugin_prefix.$this->table;
    }

    public function subscribers()
    {

        return $this->belongsToMany('Wlwcn_Eloquent\SubscriberEM', $this->mwpdb->prefix.$this->plugin_prefix.'email_address_message', 'message_id', 'subscriber_id');
    }
}
