<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    // حساب الرصيد (المدفوع - الإجمالي)
    public function getBalanceAttribute()
    {
        $total  = $this->purchases()->sum('total_amount');
        $paid   = $this->purchases()->sum('paid_amount');
        return $paid - $total;
    }
}
