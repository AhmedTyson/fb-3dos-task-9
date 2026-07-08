<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
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
        $orders = $this->orderService->getUserOrders(
            $request->user(),
            (int) $request->query('per_page', 10)
        );

        return response()->json([
            'message' => 'Orders fetched successfully',
            'data'    => UserOrderResource::collection($orders)->response()->getData(true),
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->checkout(
            $request->user(),
            $request->validated('shipping_address'),
            $request->validated('payment_method')
        );

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
