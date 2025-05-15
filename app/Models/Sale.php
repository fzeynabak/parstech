<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'product_id', 'quantity', 'total_price', 'sale_date'
    ];

    // ارتباط با فروشنده
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    // ارتباط با محصول
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
