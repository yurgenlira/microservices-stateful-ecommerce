<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\InventoryStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property int $quantity
 * @property int $reserved
 */
class InventoryStock extends Model
{
    /** @use HasFactory<InventoryStockFactory> */
    use HasFactory;

    protected static string $factory = InventoryStockFactory::class;

    protected $fillable = [
        'product_id',
        'quantity',
        'reserved',
    ];
}
