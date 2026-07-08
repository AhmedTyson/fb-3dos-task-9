<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->query('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->query('min_price'), function ($query, $minPrice) {
                $query->where('base_price', '>=', $minPrice);
            })
            ->when($request->query('max_price'), function ($query, $maxPrice) {
                $query->where('base_price', '<=', $maxPrice);
            })
            ->paginate((int) $request->query('per_page', 10));

        return response()->json([
            'message' => 'Products fetched successfully',
            'data'    => ProductResource::collection($products)->response()->getData(true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'size'        => 'required|string|max:50',
            'base_price'  => 'required|numeric|min:0',
            'in_stock'    => 'required|boolean',
            'images'      => 'nullable|array',
        ]);

        $product = Product::create($validated);
        $product->load('category');

        return response()->json([
            'message' => 'Product created successfully',
            'data'    => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return response()->json([
            'message' => 'Product fetched successfully',
            'data'    => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
            'data'    => null,
        ]);
    }
}
