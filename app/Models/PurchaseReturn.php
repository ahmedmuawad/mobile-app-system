<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = ['purchase_id','branch_id','supplier_id','user_id','total'];

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
