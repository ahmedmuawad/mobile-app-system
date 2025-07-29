<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'note',
        'repair_id',
        'sale_id', // ✅ أضفناه
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class); // ✅ العلاقة بين الدفع والفاتورة
    }
}
