<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "category" => ["id"=>$this->category?->id,
                            "name"=>$this->category?->name],
            "product_id" => $this->id,
            "product_name" => $this->name,
            "product_description" => $this->description,
            "product_size" => $this->size,
            "product_base_price" => $this->base_price,
            "is_product_in_stock" => $this->in_stock,
            "product_images" => $this->images,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
