<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'branch_id',
        'product_id',
        'spare_part_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'qty_before',
        'qty_change',
        'qty_after',
        'user_id',
        'note',
    ];
}
