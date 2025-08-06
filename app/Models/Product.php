<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        //'branch_id',  // تم التعليق لأنها في العلاقة وليس في المنتج مباشرة
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // علاقة many-to-many مع بيانات pivot لكل فرع
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

    // السعر بعد الضريبة (عام)
    public function getFinalPriceAttribute()
    {
        if ($this->is_tax_included) {
            return $this->sale_price;
        }

        $taxRate = $this->tax_percentage ?? 0;
        return $this->sale_price + ($this->sale_price * ($taxRate / 100));
    }

    // السعر بعد الضريبة حسب الفرع
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

    /**
     * تحقق من وجود كمية كافية في فرع معين
     *
     * @param int $branchId
     * @param int|float $requiredQty
     * @return bool
     */
    public function hasSufficientStockInBranch($branchId, $requiredQty)
    {
        $branch = $this->branches()->where('branch_id', $branchId)->first();

        if (!$branch) {
            return false; // لا يوجد المنتج في هذا الفرع
        }

        $stock = $branch->pivot->stock ?? 0;

        return $stock >= $requiredQty;
    }

    /**
     * تحديث كمية المخزون في فرع معين
     *
     * @param int $branchId
     * @param int|float $qtyChange (مثبت أو سالب)
     * @return bool
     */
    public function updateStockInBranch($branchId, $qtyChange)
    {
        // الحصول على السجل pivot الحالي
        $branchPivot = $this->branches()->where('branch_id', $branchId)->first();

        if (!$branchPivot) {
            // إذا لم يكن موجودًا، يمكن إنشاء السجل (حسب منطق العمل)
            // هنا نرجع false
            return false;
        }

        $currentStock = $branchPivot->pivot->stock ?? 0;
        $newStock = $currentStock + $qtyChange;

        if ($newStock < 0) {
            // لا تسمح برصيد سلبي
            return false;
        }

        // تحديث كمية المخزون في جدول pivot
        $this->branches()->updateExistingPivot($branchId, ['stock' => $newStock]);

        return true;
    }

    /**
     * تحديث سعر الشراء في فرع معين (مثلاً لحساب متوسط السعر)
     *
     * @param int $branchId
     * @param float $newPurchasePrice
     * @return bool
     */
    public function updatePurchasePriceInBranch($branchId, $newPurchasePrice)
    {
        $branchPivot = $this->branches()->where('branch_id', $branchId)->first();

        if (!$branchPivot) {
            return false;
        }

        $this->branches()->updateExistingPivot($branchId, ['purchase_price' => $newPurchasePrice]);

        return true;
    }
}
