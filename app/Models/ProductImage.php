<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images'; // Explicitly define table name

    protected $fillable = [
        'product_unique_id',
        'product_image',
        'type',
        'status',
        'is_deleted',
    ];
}
