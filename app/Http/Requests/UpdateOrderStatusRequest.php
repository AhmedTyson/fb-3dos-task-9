<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $newStatus = OrderStatus::tryFrom($this->status);
            $order = $this->route('order');

            if ($newStatus && $order && !$order->canTransitionTo($newStatus)) {
                $validator->errors()->add(
                    'status',
                    "Cannot transition order from {$order->status->value} to {$newStatus->value}."
                );
            }
        });
    }
}
