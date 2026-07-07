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

    // -------------------------------------------------------------------------
    // GET /admin/orders
    // -------------------------------------------------------------------------
    public function index(AdminOrderIndexRequest $request): JsonResponse
    {
        $orders = $this->orderService->getAdminOrders(
            $request->validated(),
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'message' => 'Orders fetched',
            'data'    => new AdminOrderCollection($orders)
        ]);
    }

    // -------------------------------------------------------------------------
    // PUT /admin/orders/{order}/status
    // -------------------------------------------------------------------------
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update(['status' => $request->validated('status')]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'data'    => new OrderStatusResource($order)
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /admin/orders/{order}/print-file?format=pdf (default)
    // -------------------------------------------------------------------------
    public function printFile(PrintFileRequest $request, Order $order): Response
    {
        return $this->pdfService->orderPrint($order);
    }

    // -------------------------------------------------------------------------
    // GET /admin/reports/sales?format=json|xlsx|pdf&from=...&to=...
    // -------------------------------------------------------------------------
    public function salesReport(SalesReportRequest $request): mixed
    {
        $format = strtolower($request->query('format', 'json'));
        
        $report = $this->salesReportService->generate(
            $request->from ?? null,
            $request->to   ?? null,
        );

        return match ($format) {
            'xlsx'  => $this->xlsxDownload($report),
            'pdf'   => $this->pdfService->salesReport($report),
            default => response()->json([
                'success' => true,
                'message' => 'Sales report generated',
                'data'    => new SalesReportResource($report)
            ]),
        };
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------
    private function xlsxDownload(array $report): BinaryFileResponse
    {
        $path = $this->xlsxService->salesReport($report);

        return response()->download($path, 'sales-report.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
