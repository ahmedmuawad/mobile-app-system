<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sale_id',
        'product_id',
        'product_name',
        'quantity',
        'sale_price',
        'purchase_price',
        'base_price',
        'tax_value',         // أضف هذا السطر
        'tax_percentage',    // أضف هذا السطر
    ];

    // علاقة الفاتورة التي ينتمي إليها هذا البند
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // علاقة المنتج الذي تم بيعه
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // علاقة الشركة
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // علاقة الفرع
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
