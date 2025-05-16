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
        'paid_amount',
        'remaining_amount',
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
        'tax' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2'
    ];

    protected $appends = [
        'final_amount',
        'status_label',
        'status_color',
        'payment_status'
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

    public function getFinalAmountAttribute()
    {
        // محاسبه مبلغ نهایی
        return $this->total_price - $this->discount + $this->tax;
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

    public function getPaymentStatusAttribute()
    {
        $finalAmount = $this->final_amount;

        if ($this->status === 'cancelled') {
            return 'لغو شده';
        }

        if ($this->paid_amount >= $finalAmount) {
            return 'پرداخت کامل';
        }

        if ($this->paid_amount > 0) {
            $remainingAmount = $finalAmount - $this->paid_amount;
            return "پرداخت ناقص (باقیمانده: " . number_format($remainingAmount) . " تومان)";
        }

        return 'پرداخت نشده';
    }

    protected static function boot()
    {
        parent::boot();

        // قبل از ذخیره رکورد
        static::saving(function ($sale) {
            // محاسبه مبلغ نهایی
            $finalAmount = $sale->total_price - $sale->discount + $sale->tax;

            // محاسبه مبلغ باقیمانده
            $sale->remaining_amount = max(0, $finalAmount - $sale->paid_amount);

            // به‌روزرسانی وضعیت پرداخت
            if ($sale->status !== 'cancelled') {
                if ($sale->paid_amount >= $finalAmount) {
                    $sale->status = 'paid';
                } else if ($sale->paid_amount > 0) {
                    $sale->status = 'pending';
                }
            }
        });
    }

    public function updatePayment($amount)
    {
        $this->paid_amount += $amount;
        $this->save();
    }

    public function recalculateTotal()
    {
        $total = 0;
        $tax = 0;
        $discount = 0;

        foreach ($this->items as $item) {
            $subtotal = $item->quantity * $item->unit_price;
            $itemDiscount = $item->discount ?? 0;
            $itemTax = $item->tax ?? 0;

            $total += $subtotal;
            $discount += $itemDiscount;
            $tax += ($subtotal - $itemDiscount) * ($itemTax / 100);
        }

        $this->update([
            'total_price' => $total,
            'discount' => $discount,
            'tax' => $tax
        ]);
    }
}
