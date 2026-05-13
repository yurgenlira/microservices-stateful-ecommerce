<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Catalog\Models\Product;
use App\Modules\Inventory\Database\Factories\InventoryStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
