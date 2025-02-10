<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_unique_id',
        'product_name',
        'description',
        'SKU',
        'category_id',
        'price_id',
        'discount_id',
        'meta',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'product_unique_id' => 'string',
        'is_deleted' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->product_unique_id = Uuid::uuid4()->toString();
        });
    }
}
