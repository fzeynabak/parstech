<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity_log';
    protected $fillable = ['user_id', 'type', 'description', 'data'];

    protected $casts = [
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIcon()
    {
        return match($this->type) {
            'sale' => 'fa-shopping-cart',
            'product' => 'fa-box',
            'customer' => 'fa-user',
            'payment' => 'fa-credit-card',
            default => 'fa-circle'
        };
    }

    public function getIconColor()
    {
        return match($this->type) {
            'sale' => '#16a34a',
            'product' => '#eab308',
            'customer' => '#0891b2',
            'payment' => '#dc2626',
            default => '#64748b'
        };
    }
}
