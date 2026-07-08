<?php

namespace App\Http\Controllers;

use App\DTOs\CheckoutData;
use App\DTOs\AddressDTO;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\UserOrderCollection;
use App\Http\Resources\UserOrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('per_page', 10), 100);

        $orders = $this->orderService->getUserOrders(
            $request->user(),
            $limit
        );

        return response()->json([
            'message' => 'Orders fetched successfully',
            'data'    => new UserOrderCollection($orders),
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->checkout(new CheckoutData(
            user: $request->user(),
            shippingAddress: AddressDTO::fromArray($request->validated('shipping_address')),
            paymentMethod: $request->validated('payment_method'),
            phone: $request->validated('phone')
        ));

        return response()->json([
            'message' => 'Order placed successfully',
            'data'    => new UserOrderResource($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load('items.product');

        return response()->json([
            'message' => 'Order details fetched successfully',
            'data'    => new UserOrderResource($order),
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        Gate::authorize('cancel', $order);

        $order = $this->orderService->cancelUserOrder($order);

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data'    => new UserOrderResource($order),
        ]);
    }
}
