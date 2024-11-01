<?php

namespace Eloquent;

use WeDevs\ORM\Eloquent\Model;

class NewsletterEM extends Model
{
    protected $table = 'email_messages';
    protected $mwpdb;
    protected $table_prefix;

    protected $fillable = ['subject', 'message', 'sent_to', 'receiver_options', 'scheduled_at', 'timezone_offset', 'sent_at'];

    protected $casts = [
            'receiver_options' => 'array',
        ];
    protected $dates = ['sent_at', 'scheduled_at'];


    function __construct()
    {
        global $wpdb;

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table_prefix = $mwpdb->prefix.wlwcn_getPluginTablePrefix();
        $this->table = $this->table_prefix.$this->table;
    }

    public function subscribers()
    {
        return $this->belongsToMany('Eloquent\SubscriberEM', $this->table_prefix.'email_address_message', 'message_id', 'subscriber_id');
    }

    public function subscribersSent()
    {
        return $this->belongsToMany('Eloquent\SubscriberEM', $this->table_prefix.'email_address_message', 'message_id', 'subscriber_id')->withPivot('sent_at')->wherePivot('sent_at', '!=', null);
    }

    public function subscribersUnsent()
    {
        return $this->belongsToMany('Eloquent\SubscriberEM', $this->table_prefix.'email_address_message', 'message_id', 'subscriber_id')->withPivot('sent_at')->wherePivot('sent_at', '=', null);
    }

    public function subscriber()
    {
        return $this->belongsTo('Eloquent\SubscriberEM', 'sent_to', 'email');
    }
}
