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
            'description' => 'required|string',
            'size'        => 'required|string|max:50',
            'base_price'  => 'required|numeric',
            'in_stock'    => 'required|boolean',
            'images'      => 'nullable|array',
        ];
    }
}
