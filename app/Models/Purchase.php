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
        'branch_id', // ✅ الحقل الخاص بالفرع
    ];

    /**
     * العلاقة مع المورد
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * العلاقة مع العناصر
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * العلاقة مع المدفوعات
     */
    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    /**
     * العلاقة مع الفرع
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * ✅ فلترة تلقائية على الفرع الحالي
     */
    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (Auth::check()) {
                $currentBranchId = session('current_branch_id');

                if ($currentBranchId) {
                    $builder->where('branch_id', $currentBranchId);
                }
            }
        });
    }
}
