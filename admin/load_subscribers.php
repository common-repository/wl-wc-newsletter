<?php

use Controllers\SubscriberController;

$sc = new SubscriberController;

$data = $sc->paginate();

require_once __DIR__.'/../views/admin/subscriber/index.php';
