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

        return [
            'period'        => ['from' => $from, 'to' => $to],
            'total_orders'  => (clone $base)->count(),
            'total_revenue' => (float) (clone $base)->sum('total'),
            'top_product'   => $this->findTopProduct($from, $to),
            'orders'        => $this->mapOrderRows(clone $base),
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
