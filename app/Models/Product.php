<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockAlert;
use App\Models\Branch;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'image',
        'purchase_price',
        'sale_price',
        'barcode',
        'stock', // مخزون عام (في حالة منتج بدون فروع)
        'category_id',
        'brand_id',
        'is_tax_included',
        'tax_percentage',
    ];

    protected $appends = ['final_price', 'low_stock_alert'];

    /*========================
        العلاقات
    ========================*/

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
                    ->withPivot([
                        'price',
                        'purchase_price',
                        'stock',
                        'is_tax_included',
                        'tax_percentage',
                        'low_stock_threshold' // ✅ أضفناها هنا

                    ])
                    ->withTimestamps();
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /*========================
        خصائص إضافية
    ========================*/

    // السعر العام بعد الضريبة
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
        $price = $this->sale_price;
        $isTaxIncluded = (bool) $this->is_tax_included;
        $tax = $this->tax_percentage ?? 0;

        if ($branchId) {
            $bp = $this->branches->firstWhere('id', $branchId);
            if ($bp && $bp->pivot) {
                $price = $bp->pivot->price ?? $price;
                $isTaxIncluded = (bool) ($bp->pivot->is_tax_included ?? $isTaxIncluded);
                $tax = $bp->pivot->tax_percentage ?? $tax;
            }
        }

        if (!$isTaxIncluded && $tax > 0) {
            return (float) $price + ((float) $price * ((float) $tax / 100));
        }

        return (float) $price;
    }

    // تنبيه المخزون المنخفض
public function getLowStockAlertAttribute()
{
    $currentBranchId = session('current_branch_id');

    if ($currentBranchId) {
        $pivot = $this->branches->firstWhere('id', $currentBranchId)?->pivot;
        if (!$pivot) {
            return false; // المنتج مش مرتبط بالفرع ده
        }
        return $pivot->low_stock_threshold > 0 && $pivot->stock <= $pivot->low_stock_threshold;
    }

    // لو مفيش فرع محدد: نفحص كل الفروع
    foreach ($this->branches as $branch) {
        if ($branch->pivot->low_stock_threshold > 0 && $branch->pivot->stock <= $branch->pivot->low_stock_threshold) {
            return true;
        }
    }

    return false;
}



    /*========================
        عمليات المخزون
    ========================*/

    public function hasSufficientStockInBranch($branchId, $requiredQty)
    {
        $branch = $this->branches()->where('branch_id', $branchId)->first();
        $stock = $branch?->pivot->stock ?? 0;
        return $stock >= $requiredQty;
    }

    public function updateStockInBranch($branchId, $qtyChange)
    {
        $branchPivot = $this->branches()->where('branch_id', $branchId)->first();
        if (!$branchPivot) return false;

        $currentStock = $branchPivot->pivot->stock ?? 0;
        $newStock = $currentStock + $qtyChange;

        if ($newStock < 0) return false;

        $this->branches()->updateExistingPivot($branchId, ['stock' => $newStock]);
        return true;
    }

    public function updatePurchasePriceInBranch($branchId, $newPurchasePrice)
    {
        $branchPivot = $this->branches()->where('branch_id', $branchId)->first();
        if (!$branchPivot) return false;

        $this->branches()->updateExistingPivot($branchId, [
            'purchase_price' => $newPurchasePrice
        ]);
        return true;
    }
}
