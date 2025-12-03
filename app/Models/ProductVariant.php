<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'compare_at_price',
        'stock',
        'weight',
        'thumbnail',
    ];

    /**
     * Parent product of this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


