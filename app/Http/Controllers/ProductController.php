<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->paginate(10);

        return response()->json([
            "status" => "success",
            "data" => ProductResource::collection($products)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->authorize('create', Product::class);

        $request->validate([
        "category_id" => "required|integer|exists:categories,id",
        "name" => "required|string|max:255",
        "description" => "nullable|string",
        "size" => "required|string|max:50",
        "base_price" => "required|numeric|min:0",
        "in_stock" => "required|boolean",
        "images" => "nullable|array",
    ]);
        $product = Product::create($request->all());

        $product->load('category');

        return response()->json([
            "status"=>"success",
            "data"=>new ProductResource($product)
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);

       return response()->json([
            "status"=>"success",
            "data"=>new ProductResource($product)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
