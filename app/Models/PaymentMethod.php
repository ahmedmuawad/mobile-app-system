<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
