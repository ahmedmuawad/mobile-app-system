<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'notes',
        'branch_id', // ✅ الحقل الجديد
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    // ✅ علاقة بالفرع
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // ✅ فلترة حسب الفرع تلقائيًا
    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (Auth::check() && session('branch_id')) {
                $builder->where('branch_id', session('branch_id'));
            }
        });
    }
}
