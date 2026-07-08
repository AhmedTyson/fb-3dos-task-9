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

        return response()->json([
            'message' => 'Orders fetched',
            'data'    => new AdminOrderCollection($orders),
        ]);
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
        $format = strtolower($request->query('format', 'json'));

        $report = $this->salesReportService->generate(
            $request->validated('from'),
            $request->validated('to'),
        );

        return match ($format) {
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
