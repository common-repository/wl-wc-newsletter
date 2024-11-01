<?php

namespace Eloquent;

use WeDevs\ORM\Eloquent\Model;

class EmailAddressMessage extends Model
{
    protected $table = 'email_address_message';
    protected $mwpdb;
    protected $table_prefix;

    function __construct()
    {
        global $wpdb;

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table_prefix = $mwpdb->prefix.wlwcn_getPluginTablePrefix();
        $this->table = $this->table_prefix.$this->table;
    }
}
