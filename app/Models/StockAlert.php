<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $fillable = ['branch_id','product_id','threshold','is_active','last_notified_at'];
}
