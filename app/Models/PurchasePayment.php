<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = ['purchase_id', 'amount', 'payment_date'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
