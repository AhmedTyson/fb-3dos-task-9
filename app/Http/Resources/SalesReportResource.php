<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period'        => $this->resource['period'],
            'total_orders'  => $this->resource['total_orders'],
            'total_revenue' => $this->resource['total_revenue'],
            'top_product'   => $this->resource['top_product'],
        ];
    }
}
