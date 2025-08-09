<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
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

    // ✅ علاقة الشركة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ✅ علاقة الفرع
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
public function products()
{
    return $this->belongsToMany(Product::class, 'sale_items')
        ->withPivot(['quantity', 'sale_price', 'purchase_price', 'tax_value', 'tax_percentage', 'cost_at_sale'])
        ->withTimestamps();
}
public function payments()
{
    return $this->hasMany(\App\Models\SalePayment::class);
}


}
