<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'date', 'due_date', 'customer_id', 'seller_id', 'currency_id',
        'reference', 'discount_amount', 'discount_percent', 'tax_percent',
        'total_amount', 'final_amount'
    ];

    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function customer() {
        return $this->belongsTo(Person::class, 'customer_id');
        return $this->belongsTo(\App\Models\Person::class, 'customer_id');
    }
    public function seller() { return $this->belongsTo(Seller::class, 'seller_id'); }
    public function currency() { return $this->belongsTo(Currency::class, 'currency_id'); }

    

}
