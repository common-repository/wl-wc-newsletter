<?php

namespace Eloquent;

use WeDevs\ORM\Eloquent\Model;

class MailingListEmailEM extends Model
{
    protected $table = 'email_address_mailing_list';
    protected $mwpdb;
    protected $table_prefix;

    function __construct()
    {
        global $wpdb;

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table_prefix = $mwpdb->prefix.wlwcn_getPluginTablePrefix();
        $this->table = $this->table_prefix.$this->table;
    }

    function subscribers()
    {
        return $this->belongsTo('Eloquent\SubscriberEM', 'email_address_id');
    }

    function mailingLists()
    {
        return $this->belongsTo('Eloquent\MailingListEM', 'email_address_id');
    }
}
