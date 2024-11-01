<?php

use Controllers\NewsletterController;

$c = new NewsletterController;
$id = sanitize_text_field($_GET['id']);
$data = $c->duplicate($id);
require_once __DIR__.'/../views/admin/newsletter/duplicate.php';
