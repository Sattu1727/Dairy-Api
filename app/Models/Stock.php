<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_id',
        'quantity_in',
        'quantity_out',
        'stock_status',
        'stock_threshold',
        'last_sold_at',
        'batch_number',
        'status',
        'is_deleted'
    ];

    protected $appends = ['current_stock']; // Add computed stock

    public function getCurrentStockAttribute()
    {
        return $this->quantity_in - $this->quantity_out;
    }
}
