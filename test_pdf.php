<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::first();
if (!$order) {
    echo "No order found\n";
    exit;
}

$pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.order-print', ['order' => $order]);
$out = $pdf->output();
echo "PDF length: " . strlen($out) . "\n";
echo substr($out, 0, 15) . "\n";
