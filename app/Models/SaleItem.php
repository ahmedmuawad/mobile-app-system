<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'quantity',
        'sale_price',
        'purchase_price'
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
}
