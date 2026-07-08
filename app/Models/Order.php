<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'status',
        'total',
        'shipping_address',
        'payment_method',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'total'            => 'float',
        'status'           => OrderStatus::class,
    ];

    protected const TRANSITIONS = [
        OrderStatus::Pending->value  => [OrderStatus::Approved,  OrderStatus::Cancelled],
        OrderStatus::Approved->value => [OrderStatus::Shipped,   OrderStatus::Cancelled],
        OrderStatus::Shipped->value  => [OrderStatus::Completed],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        if ($this->status === $newStatus) {
            return false;
        }

        $allowed = static::TRANSITIONS[$this->status->value] ?? [];

        return in_array($newStatus, $allowed, strict: true);
    }
}
