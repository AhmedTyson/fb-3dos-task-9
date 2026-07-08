<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status?->value,
            'total'            => $this->total,
            'shipping_address' => $this->shipping_address,
            'payment_method'   => $this->payment_method,
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
