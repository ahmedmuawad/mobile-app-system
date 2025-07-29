<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'device_type',
        'problem_description',
        'repair_cost',
        'discount',
        'total',
        'status',
        'paid',
        'remaining',
        'device_condition',
        'repair_type',
        'delivery_status' // ✅ حالة تسليم الجهاز
    ];

    // ✅ العلاقة مع العميل
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // ✅ العلاقة مع قطع الغيار
    public function spareParts()
    {
        return $this->belongsToMany(Product::class, 'repair_spare_part', 'repair_id', 'spare_part_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // ✅ العلاقة مع المدفوعات
    public function payments()
    {
        return $this->hasMany(CustomerPayment::class, 'repair_id');
    }

    // ✅ العلاقة مع المصروفات (Polymorphic)
    public function expenses()
    {
        return $this->morphMany(Expense::class, 'expensable');
    }

    // 💰 Accessor: المبلغ المدفوع
    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount'); 
    }

    // 💸 Accessor: المتبقي
    public function getRemainingAmountAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    // 🖁️ دالة استرجاع المبلغ ورفض الجهاز
    public function rejectAndRefund()
    {
        $paid = $this->paid_amount;

        // حذف كل المدفوعات المرتبطة
        $this->payments()->delete();

        // تسجيل المصروفات الخاصة بالمبلغ المرتجع
        if ($paid > 0) {
            $this->expenses()->create([
                'name' => 'استرجاع مبلغ', // إضافة الاسم المطلوب
                'amount' => $paid,
                'description' => 'استرجاع مبلغ للعميل بسبب رفض الجهاز',
                'expensable_id' => $this->id,
                'expensable_type' => Repair::class,
            ]);
        }

        // تصفير الفاتورة
        $this->update([
            'repair_cost' => 0,
            'discount' => 0,
            'total' => 0,
            'paid' => 0,
            'remaining' => 0,
        ]);
    }
}
