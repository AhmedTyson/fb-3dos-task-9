<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;
use Illuminate\Support\Facades\Gate;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::with('items.product')->where('user_id', $request->user()->id)->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart is empty',
                'data'    => null,
            ], 200);
        }

        return response()->json([
            'message' => 'Cart retrieved successfully',
            'data'    => new CartResource($cart),
        ], 200);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $product = Product::findOrFail($validated['product_id']);

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->quantity += $validated['quantity'];
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $validated['quantity'],
            ]);
        }

        $item->load('product');

        return response()->json([
            'message' => 'Product added to cart successfully',
            'data'    => new CartItemResource($item),
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        $item->load('cart');
        Gate::authorize('update', $item);

        $item->quantity = $request->validated('quantity');
        $item->save();

        $item->load('product');

        return response()->json([
            'message' => 'Cart item updated successfully',
            'data'    => new CartItemResource($item),
        ], 200);
    }

    public function destroy(CartItem $item): JsonResponse
    {
        $item->load('cart');
        Gate::authorize('delete', $item);

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully',
            'data'    => null,
        ], 200);
    }
}
