<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

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
                'purchase_price',
                'stock',
                'is_tax_included',
                'tax_percentage',
            ])
            ->withTimestamps();
    }

    // ✅ السعر بعد الضريبة
    public function getFinalPriceAttribute()
    {
        if ($this->is_tax_included) {
            return $this->sale_price;
        }

        $taxRate = $this->tax_percentage ?? 0;
        return $this->sale_price + ($this->sale_price * ($taxRate / 100));
    }

    // ✅ السعر بعد الضريبة حسب الفرع
    public function getFinalPriceForBranch($branchId = null)
    {
        if (!$branchId) {
            return $this->final_price;
        }

        $branch = $this->branches->firstWhere('id', $branchId);

        if ($branch && $branch->pivot) {
            $price = $branch->pivot->price;
            $isTaxIncluded = $branch->pivot->is_tax_included;
            $taxPercentage = $branch->pivot->tax_percentage ?? 0;

            return $isTaxIncluded ? $price : $price + ($price * ($taxPercentage / 100));
        }

        return $this->final_price;
    }
}
