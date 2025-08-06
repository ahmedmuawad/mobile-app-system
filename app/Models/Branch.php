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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * علاقة Many-to-Many مع المنتجات باستخدام pivot (مع السعر والمخزون والضريبة)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'price',
                'purchase_price',     // ✅ تم إضافته
                'stock',
                'is_tax_included',    // ✅ تم إضافته
                'tax_percentage'      // ✅ تم إضافته
            ])
            ->withTimestamps();
    }
}
