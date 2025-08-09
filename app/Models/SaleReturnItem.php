<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = ['sale_return_id','product_id','branch_id','quantity','price'];
}
