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
            'id'            => $this->id,
            'phone'         => $this->phone,
            'customer_name' => $this->user?->name,
            'status'        => $this->status?->value,
            'total'         => $this->total,
            'created_at'    => $this->created_at->toIso8601String(),
        ];
    }
}
