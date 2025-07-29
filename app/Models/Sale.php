<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'total',
        'profit',
        'discount',
        'paid',
        'remaining',
    ];

    // علاقة العميل (قد تكون null لو العميل يدوي)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // علاقة بنود البيع (المنتجات المباعة في هذه الفاتورة)
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // ✅ علاقة الدفعات المرتبطة بالفاتورة
    public function customerPayments()
    {
    return $this->hasMany(CustomerPayment::class);
    }
}
