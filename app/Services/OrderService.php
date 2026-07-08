<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    public function getAdminOrders(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Order::with('user:id,name');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['min_total'])) {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (!empty($filters['max_total'])) {
            $query->where('total', '<=', $filters['max_total']);
        }

        return $query->latest()->paginate($perPage);
    }
}
