<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Models;

use App\Modules\Ordering\Database\Factories\OrderFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property string $total
 * @property array<string, mixed> $shipping_address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
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

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
