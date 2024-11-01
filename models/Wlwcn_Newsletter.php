<?php

namespace Models;

require_once 'Wlwcn_Model.php';

class Wlwcn_Newsletter extends Wlwcn_Model
{
    protected $table = 'email_messages';
    protected $array = ['receiver_options'];

    protected $with_trashed = true;
}
