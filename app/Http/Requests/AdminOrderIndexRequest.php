<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminOrderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'    => ['nullable', Rule::enum(OrderStatus::class)],
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'min_total' => 'nullable|numeric|min:0',
            'max_total' => 'nullable|numeric|min:0|gte:min_total',
            'per_page'  => 'nullable|integer|min:1|max:100',
        ];
    }
}
