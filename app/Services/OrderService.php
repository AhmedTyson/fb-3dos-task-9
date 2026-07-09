<?php

namespace App\Services;

use App\DTOs\CheckoutData;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderService
{
    public function checkout(CheckoutData $data): Order
    {
        $cart = Cart::firstOrCreate(['user_id' => $data->user->id]);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            throw new UnprocessableEntityHttpException('Cart is empty');
        }

        return DB::transaction(function () use ($cart, $data) {
            $total = $cart->items->sum(function ($item) {
                if (!$item->product) return 0;
                return $item->quantity * $item->product->base_price;
            });

            $order = Order::create([
                'user_id'          => $data->user->id,
                'phone'            => $data->phone,
                'status'           => OrderStatus::Pending,
                'total'            => $total,
                'shipping_address' => $data->shippingAddress->toArray(),
                'payment_method'   => $data->paymentMethod,
            ]);

            $orderItems = $cart->items->filter(fn($item) => $item->product)->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->product->base_price,
                    'subtotal'   => $item->quantity * $item->product->base_price,
                ];
            });

            $order->items()->createMany($orderItems->toArray());

            $cart->items()->delete();

            return $order->load('items.product');
        });
    }

    public function getUserOrders(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->orders()->latest()->paginate($perPage);
    }

    public function cancelUserOrder(Order $order): Order
    {
        if (!$order->canTransitionTo(OrderStatus::Cancelled)) {
            throw new UnprocessableEntityHttpException('Order cannot be cancelled in its current status');
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        return $order;
    }

    public function getAdminOrders(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Order::with('user:id,name,email', 'items');

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
