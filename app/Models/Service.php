<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'title',
        'service_code',
        'service_category_id',
        'unit',
        'price',
        'tax',
        'execution_cost',
        'short_description',
        'description',
        'image',
        'is_active',
        'is_vat_included',
        'is_discountable',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
