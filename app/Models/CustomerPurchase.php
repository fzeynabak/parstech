<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPurchase extends Model
{
    protected $fillable = [
        'customer_id', 'invoice_id', 'total_amount', 'purchase_date'
    ];

    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
