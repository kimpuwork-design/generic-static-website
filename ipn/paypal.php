<?php
// PayPal IPN endpoint

// IPN should be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Load config and setup DB
$config = require dirname(__DIR__) . '/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'UTC');
require_once dirname(__DIR__) . '/app/Database.php';
require_once dirname(__DIR__) . '/app/PayPalIPN.php';

$db = new Database($config['db']);
$ipn = new PayPalIPN($db, $config);
$ipn->handle($_POST);

// Respond HTTP 200 to PayPal
http_response_code(200);
echo "OK";