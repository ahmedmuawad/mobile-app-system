<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
     protected $fillable = ['repair_id', 'amount', 'payment_date'];

    public function repair()
    {
        return $this->belongsTo(Repair::class);
    }
}
