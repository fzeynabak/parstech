<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_code', 'first_name', 'last_name', 'nickname', 'mobile', 'image', 'code_editable',
        'company_name', 'title', 'national_code', 'economic_code', 'registration_number',
        'branch_code', 'description'
    ];

    protected $appends = ['display_name'];

    public function getDisplayNameAttribute()
    {
        if (!empty($this->company_name)) {
            return $this->company_name;
        }

        $name = trim($this->first_name . ' ' . $this->last_name);
        if (!empty($name)) {
            return $name;
        }

        return $this->nickname ?? 'بدون نام';
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: ($this->company_name ?? $this->nickname ?? 'بدون نام');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function salesTotal()
    {
        return $this->sales()->sum('total_price');
    }

    public function salesCount()
    {
        return $this->sales()->count();
    }

    public function lastSale()
    {
        return $this->sales()->latest()->first();
    }

    public function bankAccounts()
    {
        return $this->hasMany(SellerBankAccount::class);
    }
}
