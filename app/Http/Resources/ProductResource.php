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
            'in_stock'    => (bool) $this->in_stock,
            'images'      => $this->images ?? [],
            'thumbnail'   => !empty($this->images) ? '/api/storage/' . ltrim(str_replace('/storage/', '', $this->images[0]), '/') . '?w=400' : null,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
