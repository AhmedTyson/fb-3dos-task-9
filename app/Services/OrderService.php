<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderService
{
    public function checkout(User $user, array $shippingAddress, string $paymentMethod): Order
    {
        $cart = Cart::with('items.product')->firstOrCreate(['user_id' => $user->id]);

        if ($cart->items->isEmpty()) {
            throw new UnprocessableEntityHttpException('Cart is empty');
        }

        return DB::transaction(function () use ($cart, $user, $shippingAddress, $paymentMethod) {
            $total = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->base_price;
            });

            $order = Order::create([
                'user_id'          => $user->id,
                'status'           => OrderStatus::Pending,
                'total'            => $total,
                'shipping_address' => $shippingAddress,
                'payment_method'   => $paymentMethod,
            ]);

            $orderItems = $cart->items->map(function ($item) {
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
