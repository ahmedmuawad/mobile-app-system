<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = ['sale_id','branch_id','customer_id','user_id','total'];

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
