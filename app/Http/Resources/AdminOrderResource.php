<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'phone'           => $this->phone,
            'customer_name'   => $this->user?->name,
            'customer_email'  => $this->user?->email,
            'status'          => $this->status?->value,
            'total'           => $this->total,
            'shipping_address' => $this->shipping_address,
            'items_count'     => $this->whenLoaded('items', fn() => $this->items->count()),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
