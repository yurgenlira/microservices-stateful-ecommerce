<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $type
 * @property int $quantity
 * @property string|null $reference_type
 * @property int|null $reference_id
 */
class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
