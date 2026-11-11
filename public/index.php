<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// กำหนดให้ basePath คือโฟลเดอร์ project2 นี้เลย
$basePath = __DIR__; 

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $basePath . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once $basePath . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());