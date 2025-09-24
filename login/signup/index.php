<?php
// Friendly route: /login/signup → index.php?route=register
// Works on cPanel without .htaccess rewrites by using a directory with index.php.

$_GET['route'] = 'register';
require __DIR__ . '/../../index.php';