<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\SalesReportRequest;
use App\Http\Requests\AdminOrderIndexRequest;
use App\Http\Requests\PrintFileRequest;
use App\Http\Resources\AdminOrderCollection;
use App\Http\Resources\OrderStatusResource;
use App\Http\Resources\SalesReportResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PdfService;
use App\Services\SalesReportService;
use App\Services\XlsxService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminOrderController extends Controller
{
    public function __construct(
        private readonly OrderService       $orderService,
        private readonly SalesReportService $salesReportService,
        private readonly PdfService         $pdfService,
        private readonly XlsxService        $xlsxService,
    ) {}

    public function index(AdminOrderIndexRequest $request): JsonResponse
    {
        $orders = $this->orderService->getAdminOrders(
            $request->validated(),
            $request->integer('per_page', 20)
        );

        return (new AdminOrderCollection($orders))
            ->response()
            ->header('X-Pagination-Total-Count', $orders->total())
            ->header('X-Pagination-Current-Page', $orders->currentPage())
            ->header('X-Pagination-Per-Page', $orders->perPage())
            ->header('X-Pagination-Last-Page', $orders->lastPage());
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update(['status' => $request->validated('status')]);

        return response()->json([
            'message' => 'Order status updated',
            'data'    => new OrderStatusResource($order),
        ]);
    }

    public function printFile(PrintFileRequest $request, Order $order): Response
    {
        return $this->pdfService->orderPrint($order);
    }

    public function salesReport(SalesReportRequest $request): Response
    {
        $from = $request->validated('from');
        $to   = $request->validated('to');

        $report = $this->salesReportService->generate($from, $to);
        $report['best_sellers']    = $this->salesReportService->bestSellers($from, $to, 10);
        $report['daily_breakdown'] = $this->salesReportService->dailyBreakdown($from, $to);

        return match (strtolower($request->query('format', 'json'))) {
            'xlsx'  => $this->xlsxDownload($report),
            'pdf'   => $this->pdfService->salesReport($report),
            default => response()->json([
                'message' => 'Sales report generated',
                'data'    => new SalesReportResource($report),
            ]),
        };
    }

    private function xlsxDownload(array $report): BinaryFileResponse
    {
        $path = $this->xlsxService->salesReport($report);

        return response()->download($path, 'sales-report.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
