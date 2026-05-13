<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Models;

use App\Modules\Ordering\Database\Factories\OrderFactory;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected static string $factory = OrderFactory::class;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
