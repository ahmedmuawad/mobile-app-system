<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class CustomerHistoryExport implements FromArray
{
    protected $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    public function array(): array
    {
        $data = [];

        // عنوان
        $data[] = ['سجل العميل: ' . $this->customer->name];
        $data[] = [];

        // المبيعات
        $data[] = ['--- المبيعات ---'];
        $data[] = ['التاريخ', 'الفرع', 'الإجمالي', 'المدفوع', 'المتبقي', 'طرق الدفع', 'المنتجات'];

        foreach ($this->customer->sales as $sale) {
            $payments = $sale->payments->map(function ($p) {
                return ($p->paymentMethod->name ?? '') . ' (' . $p->amount . ')';
            })->implode(', ');

            $products = $sale->products->map(function ($p) {
                return $p->name . ' (' . $p->pivot->quantity . ')';
            })->implode(', ');

            $data[] = [
                $sale->created_at->format('Y-m-d'),
                $sale->branch->name ?? '-',
                $sale->total,
                $sale->paid,
                $sale->remaining,
                $payments,
                $products
            ];
        }

        $data[] = [];
        $data[] = ['--- الصيانة ---'];
        $data[] = ['التاريخ', 'نوع الجهاز', 'المشكلة', 'الحالة', 'الإجمالي', 'المدفوع', 'المتبقي', 'طرق الدفع', 'قطع الغيار'];

        foreach ($this->customer->repairs as $repair) {
            $payments = $repair->payments->map(function ($p) {
                return ($p->paymentMethod->name ?? '') . ' (' . $p->amount . ')';
            })->implode(', ');

            $parts = $repair->spareParts->map(function ($p) {
                return $p->name . ' (' . $p->pivot->quantity . ')';
            })->implode(', ');

            $data[] = [
                $repair->created_at->format('Y-m-d'),
                $repair->device_type,
                $repair->problem_description,
                $repair->status,
                $repair->total,
                $repair->paid,
                $repair->remaining,
                $payments,
                $parts
            ];
        }

        return $data;
    }
}
