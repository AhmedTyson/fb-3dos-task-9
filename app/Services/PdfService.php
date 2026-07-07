<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    /**
     * Generate and download a sales-report PDF.
     *
     * @param array $report  Output of SalesReportService::generate()
     */
    public function salesReport(array $report): Response
    {
        return Pdf::loadView('pdf.sales-report', ['report' => $report])
            ->download('sales-report.pdf');
    }

    /**
     * Generate and download an order print PDF.
     */
    public function orderPrint(Order $order): Response
    {
        $order->load(['user:id,name,email', 'items.product:id,name']);

        return Pdf::loadView('pdf.order-print', ['order' => $order])
            ->download("order-{$order->id}.pdf");
    }
}
