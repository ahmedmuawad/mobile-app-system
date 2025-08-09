<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $fillable = [
        'branch_id',
        'product_id',
        'threshold',
        'is_active',
        'last_notified_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    // العلاقة مع المنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // العلاقة مع الفرع
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
