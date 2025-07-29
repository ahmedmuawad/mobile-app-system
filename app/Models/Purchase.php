<?php

// app/Models/Purchase.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id', 'purchase_date', 'total_amount', 'paid_amount', 'remaining_amount', 'notes'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function payments()
{
    return $this->hasMany(PurchasePayment::class);
}

}

