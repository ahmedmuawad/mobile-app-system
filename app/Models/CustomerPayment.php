<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'amount',
        'note',
        'repair_id',
        'sale_id',
        'payment_date',
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
        return $this->belongsTo(Sale::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    public function paymentMethod()
{
    return $this->belongsTo(\App\Models\PaymentMethod::class);
}
public function history(Customer $customer)
{
    $customer->load([
        'sales.products',
        'sales.payments.paymentMethod',
        'repairs.spareParts',
        'repairs.payments.paymentMethod'
    ]);

    return view('customers.history', compact('customer'));
}


}
