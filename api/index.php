<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

try {
    // Bootstrap Laravel and handle the request...
    $app = require_once __DIR__.'/../bootstrap/app.php';

    // Ensure Vercel uses /tmp for storage
    $app->useStoragePath($_ENV['APP_STORAGE'] ?? '/tmp/storage');

    // Create required storage directories on Vercel
    $storagePath = $app->storagePath();
    $directories = [
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/framework/views',
        $storagePath . '/logs',
    ];

    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Vercel Deployment Error</h1>";
    echo "<pre>" . (string)$e . "</pre>";
}
