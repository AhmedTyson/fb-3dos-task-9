<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/admin/reports/sales?format=xlsx', 'GET');
$user = App\Models\User::first();
$app->make('auth')->guard('api')->setUser($user);

$controller = $app->make(App\Http\Controllers\AdminOrderController::class);
// Bypass middleware for direct test, but we need the form request to resolve.
// FormRequests require real request routing, so let's hit it via kernel handle.
