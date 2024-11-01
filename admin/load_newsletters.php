<?php

use Controllers\NewsletterController;

$c = new NewsletterController;

$items = $c->paginate();

require_once __DIR__.'/../views/admin/newsletter/index.php';
