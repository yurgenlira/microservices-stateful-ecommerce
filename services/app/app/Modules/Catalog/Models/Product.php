<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use App\Modules\Catalog\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $price
 * @property bool $is_active
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use SoftDeletes;

    protected static string $factory = ProductFactory::class;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sku',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
