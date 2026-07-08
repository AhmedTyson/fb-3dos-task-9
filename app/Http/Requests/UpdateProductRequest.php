<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'size'        => 'required|string|max:50',
            'base_price'  => 'required|numeric|min:0',
            'in_stock'    => 'required|boolean',
            'images'      => 'nullable|array',
        ];
    }
}
