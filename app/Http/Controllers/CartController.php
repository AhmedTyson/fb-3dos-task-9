<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;

class CartController extends Controller
{
    /**
     * Get the current user's cart contents.
     */
    public function index(Request $request)
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

    /**
     * Add a product to the cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        $item = $cart->items()
             ->where('product_id', $product->id)
             ->first();

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

        // Load product relation to ensure CartItemResource can display it if needed
        $item->load('product');

        return response()->json([
            'message' => 'Product added to cart successfully',
            'data'    => new CartItemResource($item),
        ], 201);
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::findOrFail($id);

        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json([
                "message" => 'Unauthorized',
            ], 403);
        }

        $item->quantity = $validated['quantity'];
        $item->save();

        $item->load('product');

        return response()->json([
            'message' => 'Cart item updated successfully',
            'data'    => new CartItemResource($item),
        ], 200);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, string $id)
    {
        $item = CartItem::findOrFail($id);

        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully'
        ], 200);
    }
}

