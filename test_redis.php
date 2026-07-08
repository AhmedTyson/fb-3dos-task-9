<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    Illuminate\Support\Facades\Cache::store('redis')->put('test', '123', 10);
    echo "REDIS_WORKS";
} catch (\Exception $e) {
    echo "REDIS_ERROR: " . $e->getMessage();
}
