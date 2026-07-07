<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AdminOrderCollection extends ResourceCollection
{
    public $collects = AdminOrderResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items'      => $this->collection,
            'pagination' => [
                'current_page' => $this->resource->currentPage(),
                'per_page'     => $this->resource->perPage(),
                'total'        => $this->resource->total(),
                'last_page'    => $this->resource->lastPage(),
            ],
        ];
    }
}
