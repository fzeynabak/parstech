<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'final_amount',
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
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2'
    ];

    protected $appends = ['formatted_date', 'payment_status'];

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

    public function getFormattedDateAttribute()
    {
        if ($this->created_at) {
            $date = Carbon::parse($this->created_at);
            return $date->format('Y/m/d H:i');
        }
        return '';
    }

    public function calculateTotals()
    {
        // محاسبه جمع کل از آیتم‌ها
        $this->total_price = $this->items->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });

        // محاسبه جمع تخفیف‌ها
        $this->discount = $this->items->sum('discount');

        // محاسبه جمع مالیات
        $this->tax = $this->items->sum(function($item) {
            $subtotal = $item->quantity * $item->unit_price - $item->discount;
            return $subtotal * ($item->tax_percent / 100);
        });

        // محاسبه مبلغ نهایی
        $this->final_amount = $this->total_price - $this->discount + $this->tax;

        // محاسبه مبلغ باقیمانده
        $this->remaining_amount = $this->final_amount - $this->paid_amount;

        $this->save();
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
        if ($this->status === 'cancelled') {
            return 'لغو شده';
        }

        if ($this->paid_amount >= $this->final_amount) {
            return 'پرداخت کامل';
        }

        if ($this->paid_amount > 0) {
            return sprintf('پرداخت جزئی (%s از %s)',
                number_format($this->paid_amount) . ' تومان',
                number_format($this->final_amount) . ' تومان'
            );
        }

        return 'پرداخت نشده';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (!$sale->issued_at) {
                $sale->issued_at = now();
            }
        });

        static::created(function ($sale) {
            $sale->calculateTotals();
        });

        static::saved(function ($sale) {
            // اگر پرداخت کامل شد، وضعیت را به‌روز کن
            if ($sale->paid_amount >= $sale->final_amount && $sale->status === 'pending') {
                $sale->update(['status' => 'paid']);
            }
        });
    }
}
