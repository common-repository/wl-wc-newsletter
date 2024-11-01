<?php

namespace Models;

class Wlwcn_Settings
{
    protected $post;

    function __construct()
    {
        $post = get_posts([
                'post_type' => 'wl-wc-newsletter',
                'numberposts' => 1
            ]);

        if(!empty($post))
        {
            $this->post = $post[0];
        }
        else
        {
            $this->post = NULL;
        }
    }

    function getSettings()
    {
        return $this->post;
    }

    function update($data=[])
    {
        if(!empty($data))
        {
            $post = $this->post;
            foreach($data as $key => $val)
            {
                update_post_meta($post->ID, $key, $val);
            }
        }

        return $post->id;
    }

    function getMetaSingle($meta_key)
    {
        return get_post_meta($this->post->ID, $meta_key, true);
    }
}
