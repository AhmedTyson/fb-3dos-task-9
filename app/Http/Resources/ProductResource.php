<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'category_id' => $this->category_id,
            'name'        => $this->name,
            'description' => $this->description,
            'size'        => $this->size,
            'base_price'  => (float) $this->base_price,
            'price'       => (float) $this->base_price,
            'stock'       => (int) $this->stock,
            'in_stock'    => $this->stock > 0,
            'images'      => $this->images ?? [],
            'thumbnail'   => !empty($this->images)
                ? (str_starts_with($this->images[0], 'http')
                    ? $this->images[0]
                    : '/api/storage/' . ltrim(str_replace('/storage/', '', $this->images[0]), '/') . '?w=400')
                : null,
            'category'    => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
