<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address'          => 'required|array',
            'shipping_address.street'   => 'required|string|max:255',
            'shipping_address.city'     => 'required|string|max:255',
            'shipping_address.zip_code' => 'required|string|max:50',
            'shipping_address.country'  => 'required|string|max:255',
            'payment_method'            => 'required|string|in:cash_on_delivery,credit_card',
        ];
    }
}
