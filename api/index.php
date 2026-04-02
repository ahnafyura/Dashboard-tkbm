<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 1. Paksa Laravel menggunakan folder /tmp (Memory Vercel)
$app->useStoragePath('/tmp/storage');

// 2. Buat otomatis kerangka folder yang dibutuhkan Laravel di dalam /tmp
$dirs = [
    '/tmp/storage/logs',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/bootstrap/cache',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 3. Jalankan Website!
$request = Illuminate\Http\Request::capture();
$response = $app->handleRequest($request);
$response->send();
$app->terminate($request, $response);