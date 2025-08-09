<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchasePayment;
use App\Models\Branch;
use App\Models\Expense;
use App\Services\StockService;

class PurchaseController extends Controller
{
    public function index()
    {
        $currentBranchId = session('current_branch_id');

        $query = Purchase::with('supplier');

        if ($currentBranchId && $currentBranchId !== 'all') {
            $query->where('branch_id', $currentBranchId);
        }

        $purchases = $query->latest()->get();
        return view('admin.views.purchases.index', compact('purchases', 'currentBranchId'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products  = Product::all();
        return view('admin.views.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'paid_amount'            => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $branch_id = session('current_branch_id');

            $purchase = Purchase::create([
                'supplier_id'     => $request->supplier_id,
                'branch_id'       => $branch_id,
                'notes'           => $request->notes,
                'total_amount'    => 0,
                'paid_amount'     => 0,
                'remaining_amount'=> 0,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = $item['quantity'];
                $price   = $item['purchase_price'];

                // تحديث المخزون من خلال الخدمة
                StockService::increaseStock(
                    $product->id,
                    $branch_id,
                    $qty,
                    'Purchase',
                    $purchase->id,
                    $price
                );

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $product->id,
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'subtotal'    => $qty * $price,
                ]);

                $totalAmount += $qty * $price;
            }

            $paidAmount = $request->input('paid_amount', 0);
            $remaining  = $totalAmount - $paidAmount;

            $purchase->update([
                'total_amount'     => $totalAmount,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remaining,
            ]);

            if ($paidAmount > 0) {
                PurchasePayment::create([
                    'purchase_id'  => $purchase->id,
                    'amount'       => $paidAmount,
                    'payment_date' => now(),
                ]);

                Expense::create([
                    'name'         => 'دفع كاش للمورد: ' . $purchase->supplier->name,
                    'description'  => 'فاتورة شراء رقم #' . $purchase->id,
                    'amount'       => $paidAmount,
                    'expense_date' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', '✅ تم حفظ الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage());
        }
    }

    public function edit(Purchase $purchase)
    {
        $currentBranchId = session('current_branch_id');
        if ($purchase->branch_id != $currentBranchId && $currentBranchId !== 'all') {
            abort(403, 'لا يمكنك تعديل فواتير من فرع آخر.');
        }

        $suppliers = Supplier::all();
        $products  = Product::all();
        $purchase->load('items');

        return view('admin.views.purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'new_payments'           => 'nullable|array',
            'new_payments.*.amount'  => 'required_with:new_payments|numeric|min:0.01',
            'new_payments.*.payment_date' => 'required_with:new_payments|date',
        ]);

        DB::beginTransaction();

        try {
            $purchase = Purchase::with('items')->findOrFail($id);
            $branchId = $purchase->branch_id;

            $currentBranchId = session('current_branch_id');
            if ($branchId != $currentBranchId && $currentBranchId !== 'all') {
                abort(403, 'لا يمكنك تعديل فواتير من فرع آخر.');
            }

            // استرجاع المخزون القديم
            foreach ($purchase->items as $oldItem) {
                StockService::decreaseStock(
                    $oldItem->product_id,
                    $branchId,
                    $oldItem->quantity,
                    'PurchaseUpdateRevert',
                    $purchase->id
                );
            }

            PurchaseItem::where('purchase_id', $purchase->id)->delete();

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = $item['quantity'];
                $price   = $item['purchase_price'];

                StockService::increaseStock(
                    $product->id,
                    $branchId,
                    $qty,
                    'PurchaseUpdate',
                    $purchase->id,
                    $price
                );

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $product->id,
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                    'subtotal'    => $qty * $price,
                ]);

                $totalAmount += $qty * $price;
            }

            $purchase->update([
                'supplier_id'  => $request->supplier_id,
                'notes'        => $request->notes,
                'total_amount' => $totalAmount,
            ]);

            if ($request->has('new_payments')) {
                foreach ($request->new_payments as $paymentData) {
                    PurchasePayment::create([
                        'purchase_id'  => $purchase->id,
                        'amount'       => $paymentData['amount'],
                        'payment_date' => $paymentData['payment_date'],
                    ]);

                    Expense::create([
                        'name'         => 'دفع كاش للمورد: ' . $purchase->supplier->name,
                        'description'  => 'فاتورة شراء رقم #' . $purchase->id,
                        'amount'       => $paymentData['amount'],
                        'expense_date' => $paymentData['payment_date'],
                    ]);
                }
            }

            $paidAmount = $purchase->payments()->sum('amount');
            $remaining  = $totalAmount - $paidAmount;

            $purchase->update([
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remaining,
            ]);

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', '✅ تم تحديث الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase)
    {
        $currentBranchId = session('current_branch_id');
        if ($purchase->branch_id != $currentBranchId && $currentBranchId !== 'all') {
            abort(403, 'لا يمكنك حذف فواتير من فرع آخر.');
        }

        DB::beginTransaction();

        try {
            foreach ($purchase->items as $item) {
                StockService::decreaseStock(
                    $item->product_id,
                    $purchase->branch_id,
                    $item->quantity,
                    'PurchaseDelete',
                    $purchase->id
                );
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', '✅ تم حذف الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $currentBranchId = session('current_branch_id');
        if ($purchase->branch_id != $currentBranchId && $currentBranchId !== 'all') {
            abort(403, 'لا يمكنك عرض فواتير من فرع آخر.');
        }

        $purchase->load(['supplier', 'items.product', 'payments']);
        return view('admin.views.purchases.show', compact('purchase'));
    }

    public function storePayment(Request $request, $purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $supplier = $purchase->supplier;

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($purchase) {
                    $remaining = $purchase->remaining_amount;
                    if ($value > $remaining) {
                        $fail('المبلغ المدفوع لا يمكن أن يتجاوز المبلغ المتبقي.');
                    }
                },
            ],
            'payment_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            PurchasePayment::create([
                'purchase_id'  => $purchase->id,
                'amount'       => round($request->amount, 2),
                'payment_date' => $request->payment_date,
            ]);

            $paidAmount = $purchase->payments()->sum('amount');
            $remaining  = max($purchase->total_amount - $paidAmount, 0);

            $purchase->update([
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remaining,
            ]);

            DB::commit();

            if (str_contains(url()->previous(), 'suppliers')) {
                $balance = $supplier->balance;

                $payments = \App\Models\PurchasePayment::whereIn(
                    'purchase_id',
                    $supplier->purchases()->pluck('id')
                )->orderByDesc('payment_date')->get();

                return view('admin.suppliers.pay_balance', compact('supplier', 'balance', 'payments'))
                    ->with('success', '✅ تم إضافة الدفعة بنجاح.');
            }

            return redirect()->back()->with('success', '✅ تم إضافة الدفعة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ حدث خطأ أثناء الإضافة: ' . $e->getMessage());
        }
    }
}
