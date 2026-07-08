<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === UserRole::Admin;
    }
}
