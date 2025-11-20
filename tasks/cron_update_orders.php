<?php
// Cron job to update order statuses.
// Set this up in cPanel -> Cron Jobs to run every 10 minutes, for example:
// php -d detect_unicode=0 /home/USERNAME/public_html/tasks/cron_update_orders.php

$config = require dirname(__DIR__) . '/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'UTC');
require_once dirname(__DIR__) . '/app/Database.php';
require_once dirname(__DIR__) . '/app/ProviderAPI.php';

$db = new Database($config['db']);
$api = new ProviderAPI($db, $config);

// Fetch recent processing orders
$orders = $db->fetchAll("SELECT id FROM orders WHERE status IN ('pending','processing') ORDER BY id DESC LIMIT 100");
foreach ($orders as $o) {
    try {
        $api->updateOrderStatus((int)$o['id']);
        echo "Updated order #" . $o['id'] . PHP_EOL;
    } catch (Throwable $e) {
        echo "Failed to update order #" . $o['id'] . " - " . $e->getMessage() . PHP_EOL;
    }
}