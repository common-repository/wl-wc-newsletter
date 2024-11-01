<?php

namespace Eloquent;

use WeDevs\ORM\Eloquent\Model;
use Cocur\Slugify\Slugify;

class MailingListEM extends Model
{
    protected $table = 'mailing_lists';
    protected $mwpdb;
    protected $table_prefix;

    function __construct()
    {
        global $wpdb;

        $this->mwpdb = $mwpdb = $wpdb;
        $this->table_prefix = $mwpdb->prefix.wlwcn_getPluginTablePrefix();
        $this->table = $this->table_prefix.$this->table;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;

        if(!isset($this->attributes['id']))
        {
            $slugify = new Slugify;
            $value = $slugify->slugify($value);

            $count = static::whereRaw("slug RLIKE '^{$value}(-[0-9]+)?$'")->count();

            if($count)
            {
                $value = "{$value}-{$count}";
            }

            $this->attributes['slug'] = $value;
        }
    }

    function subscribers()
    {
        return $this->belongsToMany('Eloquent\SubscriberEM', $this->table_prefix.'email_address_mailing_list', 'mailing_list_id', 'email_address_id');
    }
}
