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
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Cart::with('items . product')->where('user_id', auth()->id())->first();
        if (! $cart){
            return response()->json([
                'message'=> 'cart is empty',
                'data' => null,
            ],200);
        }
        return response()->json([
            'message' => 'Cart retrieved successfully',
            'data' => new CartResource($cart),
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated= request()->validate([
            'product_id' => 'required | exists:products,id',
            'quantity' => 'required | integer | min:1'
        ]);
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);
        $product = Product::findOrFail($validated['product_id']);
       $item = $cart->items()
             ->where('product_id', $product->id)
             ->first();
        if ($item){
            $item->quantity += $validated['quantity'];
            $item->save();
        }else{
            $item = CartItem::create([
                'cart_id'=> $cart->id,
                'product_id'=> $product ->id,
                'quantity'=>$validated['quantity'],
            ]);
        }
        return response()->json([
            'message' =>'Product added to cart successfully',
            'data'=> new CartResource($item),
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated= request()->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $item = CartItem::findOrFail($id);
        if ($item->cart->user_id !=auth()->id()){
            return response()->json([
                "message" =>'Unauthorized',
            ],403);
        }
        $item->quantity = $validated['quantity'];
        $item->save();
        return response()->json([
            'message' => 'Cart item updated successfully',
            'data' => new CartResource($item),
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item= CartItem::findOrFail($id);
        if ($item->cart->user_id != auth()->id()) {
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
