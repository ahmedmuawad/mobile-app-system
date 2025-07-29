<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'device_type',
        'problem_description',
        'repair_cost',
        'discount',
        'total',
        'status',
        'paid',
        'remaining',
        'device_condition',
        'repair_type'
    ];

    /**
     * علاقة الربط مع العميل
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * علاقة Many-to-Many مع قطع الغيار باستخدام pivot (مع الكمية)
     */
    public function spareParts()
    {
        return $this->belongsToMany(Product::class, 'repair_spare_part', 'repair_id', 'spare_part_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * علاقة الربط مع المدفوعات
     */
    public function payments()
    {
        return $this->hasMany(CustomerPayment::class, 'repair_id');
    }
}
