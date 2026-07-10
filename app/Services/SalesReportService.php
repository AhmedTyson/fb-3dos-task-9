<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * @return array{period: array, total_orders: int, total_revenue: float, top_product: array|null, orders: Collection}
     */
    public function generate(?string $from, ?string $to): array
    {
        $base = $this->baseQuery($from, $to);
        $totalOrders  = (clone $base)->count();
        $totalRevenue = (float) (clone $base)->sum('total');

        return [
            'period'          => ['from' => $from, 'to' => $to],
            'total_orders'    => $totalOrders,
            'total_revenue'   => $totalRevenue,
            'avg_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            'total_users'     => \App\Models\User::count(),
            'top_product'     => $this->findTopProduct($from, $to),
            'orders'          => $this->mapOrderRows(clone $base),
        ];
    }

    private function baseQuery(?string $from, ?string $to): Builder
    {
        return Order::query()
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    private function findTopProduct(?string $from, ?string $to): ?array
    {
        $product = Product::select(
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

        if (!$product) {
            return null;
        }

        return [
            'id'         => $product->id,
            'name'       => $product->name,
            'units_sold' => (int) $product->units_sold,
        ];
    }

    public function bestSellers(?string $from, ?string $to, int $limit = 10): array
    {
        return Product::select(
            'products.id',
            'products.name',
            'products.base_price',
            'products.images',
            DB::raw('SUM(order_items.quantity) as units_sold'),
            DB::raw('SUM(order_items.subtotal) as total_revenue')
        )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($from, fn($q) => $q->whereDate('orders.created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('orders.created_at', '<=', $to))
            ->groupBy('products.id', 'products.name', 'products.base_price', 'products.images')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function dailyBreakdown(?string $from, ?string $to): array
    {
        return $this->breakdown($from, $to, 'daily');
    }

    public function breakdown(?string $from, ?string $to, string $mode = 'daily'): array
    {
        [$selectDate, $groupDate] = match ($mode) {
            'weekly' => [
                DB::raw("strftime('%Y-W%W', created_at) as date"),
                DB::raw("strftime('%Y-W%W', created_at)"),
            ],
            'monthly' => [
                DB::raw("strftime('%Y-%m', created_at) as date"),
                DB::raw("strftime('%Y-%m', created_at)"),
            ],
            default => [
                DB::raw("DATE(created_at) as date"),
                DB::raw("DATE(created_at)"),
            ],
        };

        return Order::query()
            ->select($selectDate, DB::raw('COUNT(*) as order_count'), DB::raw('COALESCE(SUM(total), 0) as revenue'))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy($groupDate)
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function mapOrderRows(Builder $query): Collection
    {
        return $query
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
    }
}
