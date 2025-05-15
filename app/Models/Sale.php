<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'reference',
        'customer_id',
        'seller_id',
        'currency_id',
        'title',
        'issued_at',
        'due_at',
        'total_price',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
