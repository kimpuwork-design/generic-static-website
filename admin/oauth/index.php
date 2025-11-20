<?php
// Friendly route: /admin/oauth → index.php?route=admin_oauth
$_GET['route'] = 'admin_oauth';
require __DIR__ . '/../../index.php';