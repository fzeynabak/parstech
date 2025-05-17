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
        'payment_status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'cancellation_reason',
        // فیلدهای پرداخت نقدی
        'cash_amount',
        'cash_reference',
        'cash_paid_at',
        // فیلدهای پرداخت کارت به کارت
        'card_amount',
        'card_reference',
        'card_number',
        'card_bank',
        'card_paid_at',
        // فیلدهای پرداخت POS
        'pos_amount',
        'pos_reference',
        'pos_terminal',
        'pos_paid_at',
        // فیلدهای پرداخت آنلاین
        'online_amount',
        'online_reference',
        'online_transaction_id',
        'online_paid_at',
        // فیلدهای چک
        'cheque_amount',
        'cheque_number',
        'cheque_bank',
        'cheque_due_date',
        'cheque_status',
        'cheque_received_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'cash_paid_at' => 'datetime',
        'card_paid_at' => 'datetime',
        'pos_paid_at' => 'datetime',
        'online_paid_at' => 'datetime',
        'cheque_received_at' => 'datetime',
        'cheque_due_date' => 'date',
        'total_price' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer',
        'final_amount' => 'integer',
        'paid_amount' => 'integer',
        'remaining_amount' => 'integer',
        'cash_amount' => 'integer',
        'card_amount' => 'integer',
        'pos_amount' => 'integer',
        'online_amount' => 'integer',
        'cheque_amount' => 'integer'
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
        $total_price = $this->items->sum(function($item) {
            return $item->quantity * $item->unit_price;
        });
        $discount = $this->items->sum('discount');
        $tax = $this->items->sum('tax');
        $final_amount = $total_price - $discount + $tax;
        $remaining_amount = $final_amount - $this->paid_amount;

        // فقط مقادیر را برگردان، نه ذخیره
        return [
            'total_price' => $total_price,
            'discount' => $discount,
            'tax' => $tax,
            'final_amount' => $final_amount,
            'remaining_amount' => $remaining_amount,
        ];
    }

    public function updatePaymentStatus()
    {
        $totalPaid = 0;

        // جمع همه پرداخت‌ها
        if ($this->cash_amount) $totalPaid += $this->cash_amount;
        if ($this->card_amount) $totalPaid += $this->card_amount;
        if ($this->pos_amount) $totalPaid += $this->pos_amount;
        if ($this->online_amount) $totalPaid += $this->online_amount;
        if ($this->cheque_amount && $this->cheque_status === 'cleared') $totalPaid += $this->cheque_amount;

        $this->paid_amount = $totalPaid;
        $this->remaining_amount = $this->final_amount - $totalPaid;

        if ($this->remaining_amount <= 0) {
            $this->remaining_amount = 0;
            $this->status = 'paid';
            $this->paid_at = $this->paid_at ?? now();
        } else {
            $this->status = 'pending';
        }

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

        static::updated(function ($sale) {
            if ($sale->wasChanged(['total_price', 'discount', 'tax']) ||
                $sale->wasChanged(['cash_amount', 'card_amount', 'pos_amount', 'online_amount', 'cheque_amount'])) {
                $sale->calculateTotals();
            }
        });
    }
}
