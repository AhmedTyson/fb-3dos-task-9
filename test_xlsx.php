<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\XlsxService();
$report = [
    'period' => ['from' => null, 'to' => null],
    'total_orders' => 5,
    'total_revenue' => 1500,
    'top_product' => ['name' => 'Mouse', 'units_sold' => 10],
    'orders' => []
];
$path = $service->salesReport($report);
echo "OK: $path\n";
