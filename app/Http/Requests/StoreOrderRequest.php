<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public const EGYPT_MOBILE_REGEX = '/^\+201[0-9]{9}$/';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = preg_replace('/[\s\-\(\)]+/', '', $this->phone);
            
            // Normalize Egyptian numbers to +20 format
            if (str_starts_with($phone, '01')) {
                $phone = '+20' . substr($phone, 1);
            } elseif (str_starts_with($phone, '201')) {
                $phone = '+' . $phone;
            }

            $this->merge([
                'phone' => $phone,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'phone'                     => ['required', 'string', 'regex:' . self::EGYPT_MOBILE_REGEX],
            'shipping_address'          => 'required|array',
            'shipping_address.street'   => 'required|string|max:255',
            'shipping_address.city'     => 'required|string|max:255',
            'shipping_address.zip_code' => 'required|string|max:50',
            'shipping_address.country'  => 'required|string|max:255',
            'payment_method'            => 'required|string|in:cash_on_delivery,credit_card',
        ];
    }
    
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be a valid Egyptian mobile number (e.g., 01012345678 or +201012345678).',
        ];
    }
}
