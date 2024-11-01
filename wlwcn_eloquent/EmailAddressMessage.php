<?php

namespace Wlwcn_Eloquent;

use WeDevs\ORM\Eloquent\Model;

class EmailAddressMessage extends Model
{
    protected $table = 'email_address_message';
    protected $mwpdb;
    protected $plugin_prefix;

    function __construct()
    {
        global $wpdb;

        $plugin_prefix = wlwcn_getPluginTablePrefix();

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table = $mwpdb->prefix.$plugin_prefix.$this->table;
    }
}
