<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'purchase_price',
        'sale_price',
        'barcode',
        'stock',
        'category_id',
        'brand_id',
        'is_tax_included',
        'tax_percentage',
    ];

    protected $appends = ['final_price'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class)
            ->withPivot([
                'price',
                'purchase_price',     // ✅ أضف هذا
                'stock',
                'is_tax_included',    // ✅ أضف هذا
                'tax_percentage'      // ✅ أضف هذا
            ])
            ->withTimestamps();
    }


    // ✅ حساب السعر بعد الضريبة (final_price)
    public function getFinalPriceAttribute()
    {
        if ($this->is_tax_included) {
            return $this->sale_price;
        }

        $taxRate = $this->tax_percentage ?? 0;
        return $this->sale_price + ($this->sale_price * ($taxRate / 100));
    }
    public function getFinalPriceForBranch($branchId = null)
    {
        // لو مفيش فرع محدد، استخدم السعر العام
        if (!$branchId) {
            return $this->final_price;
        }

        // تحميل بيانات الفرع المحدد من العلاقة المحملة مسبقًا
        $branch = $this->branches->firstWhere('id', $branchId);

        if ($branch && $branch->pivot) {
            $price = $branch->pivot->price;
            $isTaxIncluded = $branch->pivot->is_tax_included;
            $taxPercentage = $branch->pivot->tax_percentage ?? 0;

            if ($isTaxIncluded) {
                return $price;
            } else {
                return $price + ($price * ($taxPercentage / 100));
            }
        }

        // fallback في حالة مفيش بيانات pivot
        return $this->final_price;
    }


}
