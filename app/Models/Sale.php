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
        'total_price',
        'discount',
        'tax',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'cancellation_reason'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2'
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده'
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'paid' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getFinalAmountAttribute()
    {
        return $this->total_price - $this->discount + $this->tax;
    }
}
