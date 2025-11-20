<?php
// Friendly route: /api → index.php?route=api
$_GET['route'] = 'api';
require __DIR__ . '/../index.php';