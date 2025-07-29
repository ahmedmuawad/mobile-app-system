<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * الحقول المسموح بتعبئتها تلقائيًا (Mass Assignment)
     */
    protected $fillable = [
        'name',             // اسم المنتج
        'image',            // صورة المنتج (اختياري)
        'purchase_price',   // سعر الشراء
        'sale_price',       // سعر البيع
        'barcode',          // الباركود (اختياري)
        'description',      // وصف المنتج (اختياري)
        'stock',            // الكمية المتوفرة (افتراضيًا 1)
        'category_id',      // التصنيف المرتبط
    ];

    /**
     * علاقة المنتج بتصنيف واحد
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
