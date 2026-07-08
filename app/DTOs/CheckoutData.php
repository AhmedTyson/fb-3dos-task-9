<?php

namespace App\DTOs;

use App\Models\User;

class CheckoutData
{
    public function __construct(
        public readonly User $user,
        public readonly AddressDTO $shippingAddress,
        public readonly string $paymentMethod,
        public readonly string $phone,
    ) {}
}
