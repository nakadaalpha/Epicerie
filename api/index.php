<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

try {
    // Set Vercel storage paths BEFORE bootstrapping
    $storagePath = '/tmp/storage';
    
    putenv("APP_STORAGE={$storagePath}");
    $_ENV['APP_STORAGE'] = $storagePath;
    
    // Override specific config paths via env
    putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
    $_ENV['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";
    
    putenv("SESSION_DRIVER=database");
    $_ENV['SESSION_DRIVER'] = "database";
    
    putenv("CACHE_STORE=database");
    $_ENV['CACHE_STORE'] = "database";
    
    // Create required storage directories on Vercel
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

    // Bootstrap Laravel and handle the request...
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $app->useStoragePath($storagePath);

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Vercel Deployment Error</h1>";
    echo "<pre>" . (string)$e . "</pre>";
}
