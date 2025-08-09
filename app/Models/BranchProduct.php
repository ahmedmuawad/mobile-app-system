<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchProduct extends Model
{
    // اسم الجدول
    protected $table = 'branch_product';

    // الجدول pivot غالبًا مفيهوش timestamps
    public $timestamps = false;

    // الحقول اللي مسموح تعبئتها
    protected $fillable = [
        'branch_id',
        'product_id',
        'price',
        'purchase_price',
        'stock',
        'is_tax_included',
        'tax_percentage',
    ];
}
