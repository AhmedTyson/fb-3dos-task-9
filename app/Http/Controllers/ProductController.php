<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('per_page', 10), 100);

        $products = Product::with('category')
            ->filter($request->only(['category_id', 'search', 'min_price', 'max_price', 'sort']))
            ->paginate($limit);

        return response()->json([
            'message' => 'Products fetched successfully',
            'data'    => new ProductCollection($products),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $data = $request->validated();
        $data['images'] = $this->handleImages($request);

        $product = Product::create($data);
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

        $data = $request->validated();
        $data['images'] = $this->handleImages($request, $product->images ?? []);
        $product->update($data);

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

    private function handleImages(Request $request, array $existing = []): array
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            return ['/storage/' . $path];
        }

        if ($request->filled('image_url')) {
            return [$request->input('image_url')];
        }

        return $existing;
    }
}
