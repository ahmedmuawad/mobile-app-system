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
        'description',
        'stock',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * علاقة Many-to-Many مع الإصلاحات باستخدام pivot (مع الكمية)
     */
    public function repairs()
    {
        return $this->belongsToMany(Repair::class, 'repair_spare_part', 'spare_part_id', 'repair_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
