<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * @param string|null $from  Date string Y-m-d
     * @param string|null $to    Date string Y-m-d
     * @return array{period: array, total_orders: int, total_revenue: float, top_product: array|null, orders: \Illuminate\Support\Collection}
     */
    public function generate(?string $from, ?string $to): array
    {
        $query = Order::query();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $totalOrders  = (clone $query)->count();
        $totalRevenue = (clone $query)->sum('total');

        $topProduct = Product::select(
            'products.id',
            'products.name',
            DB::raw('SUM(order_items.quantity) as units_sold')
        )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($from, fn($q) => $q->whereDate('orders.created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('orders.created_at', '<=', $to))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->first();

        // Full order rows for export (XLSX/PDF)
        $orders = (clone $query)
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn(Order $o) => [
                'id'            => $o->id,
                'customer_name' => $o->user?->name ?? 'N/A',
                'status'        => $o->status->value,
                'total'         => $o->total,
                'created_at'    => $o->created_at->toDateString(),
            ]);

        return [
            'period'        => ['from' => $from, 'to' => $to],
            'total_orders'  => $totalOrders,
            'total_revenue' => (float) $totalRevenue,
            'top_product'   => $topProduct ? [
                'id'         => $topProduct->id,
                'name'       => $topProduct->name,
                'units_sold' => (int) $topProduct->units_sold,
            ] : null,
            'orders'        => $orders,
        ];
    }
}
