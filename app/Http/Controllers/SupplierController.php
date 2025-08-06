<?php
// app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\PurchasePayment;
use App\Models\Purchase;
use App\Models\Branch;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('purchases')->get();
        return view('admin.views.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Supplier::create($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', '✅ تم إضافة المورد بنجاح.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.views.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier->update($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', '✅ تم تحديث بيانات المورد.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', '🗑️ تم حذف المورد.');
    }
        public function payBalanceForm(Supplier $supplier)
    {
        $balance = $supplier->balance;
        if ($balance >= 0) {
            return redirect()->route('admin.suppliers.index')->with('error', 'المورد ليس عليه مبالغ مستحقة.');
        }
        return view('admin.views.suppliers.pay-balance', compact('supplier', 'balance'));
    }

    // استقبال دفعة من المورد وتوزيعها على الفواتير المفتوحة
public function payBalance(Request $request, Supplier $supplier)
{
    $request->validate([
        'amount' => [
            'required',
            'numeric',
            'min:0.01',
            function ($attr, $value, $fail) use ($supplier) {
                if ($value > abs($supplier->balance)) {
                    $fail('المبلغ أكبر من الرصيد المستحق على المورد.');
                }
            }
        ],
        'payment_date' => 'required|date',
    ]);

    DB::beginTransaction();
    try {
        $amountToPay = round($request->amount, 2); // توخي الدقة المالية
        $paymentDate = $request->payment_date;

        // جلب فواتير المورد التي عليها مبالغ مستحقة (الأقدم أولاً)
        $openPurchases = $supplier->purchases()
            ->where('remaining_amount', '>', 0)
            ->orderBy('purchase_date')
            ->get();

        foreach ($openPurchases as $purchase) {
            if ($amountToPay <= 0) break;

            $remaining = $purchase->remaining_amount;
            $payOnThis = min($remaining, $amountToPay);

            // إنشاء دفعة جديدة لهذه الفاتورة
            \App\Models\PurchasePayment::create([
                'purchase_id'  => $purchase->id,
                'amount'       => $payOnThis,
                'payment_date' => $paymentDate,
            ]);

            // تحديث بيانات الفاتورة
            $purchase->increment('paid_amount', $payOnThis);
            $purchase->decrement('remaining_amount', $payOnThis);

            if ($purchase->remaining_amount < 0) {
                $purchase->update(['remaining_amount' => 0]);
            }

            $amountToPay -= $payOnThis;
        }

        DB::commit();

        // جلب الرصيد الحالي بعد الدفع
        $balance = $supplier->balance;

        // جلب سجل الدفعات
        $payments = \App\Models\PurchasePayment::whereIn(
            'purchase_id',
            $supplier->purchases()->pluck('id')
        )->orderByDesc('payment_date')->get();

return view('admin.views.suppliers.pay-balance', compact('supplier', 'balance', 'payments'))
    ->with('success', '✅ تم تسجيل الدفعة وتوزيعها بنجاح على الفواتير المفتوحة.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', '❌ حدث خطأ أثناء تنفيذ عملية الدفع: ' . $e->getMessage());
    }
}

}
