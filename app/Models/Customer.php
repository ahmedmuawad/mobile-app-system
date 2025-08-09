<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email'];

    // علاقة المبيعات لهذا العميل
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
public function repairs()
{
    return $this->hasMany(\App\Models\Repair::class);
}
public function getTotalDueAttribute()
{
    $sales_due = $this->sales->sum(function ($sale) {
        $remaining = ($sale->total ?? 0) - ($sale->paid ?? 0);
        return $remaining > 0 ? $remaining : 0;
    });

    $repairs_due = $this->repairs->sum(function ($repair) {
        $remaining = ($repair->total ?? 0) - ($repair->paid ?? 0);
        return $remaining > 0 ? $remaining : 0;
    });

    return $sales_due + $repairs_due;
}


}
