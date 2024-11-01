<?php

use Controllers\NewsletterController;

$c = new NewsletterController;
$data = $c->create();

require_once __DIR__.'/../views/admin/newsletter/create.php';
