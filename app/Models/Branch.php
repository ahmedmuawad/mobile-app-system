<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'phone',
        'is_main',
        'is_active'
    ];

    /**
     * الشركة المالكة للفرع
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * المستخدمين التابعين للفرع (Many to Many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * المنتجات الموجودة في هذا الفرع (Many to Many)
     * عبر جدول pivot: branch_product
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'price',
                'purchase_price',
                'stock',
                'is_tax_included',
                'tax_percentage'
            ])
            ->withTimestamps();
    }

    /**
     * عمليات الشراء المرتبطة بالفرع
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
