<?php

use Controllers\SubscriberController;

$sc = new SubscriberController;
$id = sanitize_text_field($_GET['id']);
$item = $sc->edit($id);

require_once __DIR__.'/../views/admin/subscriber/edit.php';
