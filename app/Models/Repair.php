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
            'spare_part_id',
            'repair_cost',
            'discount',
            'total',
            'status',
            'paid',        // ✅ أضف هذا
            'remaining',   // ✅ وأضف هذا أيضًا
        ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sparePart()
    {
        return $this->belongsTo(Product::class, 'spare_part_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\CustomerPayment::class, 'repair_id');
    }
}
