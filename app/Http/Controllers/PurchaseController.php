<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchasePayment; // أضف هذا في الأعلى


class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->latest()->get();
        return view('admin.views.purchases.index', compact('purchases'));
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
            $purchase = Purchase::create([
                'supplier_id'     => $request->supplier_id,
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

                $oldStock = $product->stock;
                $oldCost  = $product->purchase_price;

                $newStock = $oldStock + $qty;
                $avgCost  = ($oldStock * $oldCost + $qty * $price) / ($newStock ?: 1);

                $product->update([
                    'stock'          => $newStock,
                    'purchase_price' => $avgCost,
                ]);

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
                // حفظ الدفع في جدول purchase_payments
                PurchasePayment::create([
                    'purchase_id'  => $purchase->id,
                    'amount'       => $paidAmount,
                    'payment_date' => now(), // يمكنك تغييره لاحقًا إلى $request->payment_date
                ]);

                // تسجيله أيضاً كمصروف
                \App\Models\Expense::create([
                    'name'         => 'دفع كاش للمورد: ' . $purchase->supplier->name,
                    'description'  => 'فاتورة شراء رقم #' . $purchase->id,
                    'amount'       => $paidAmount,
                    'expense_date' => now(), // نفس التاريخ
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
            'paid_amount'            => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $purchase = Purchase::findOrFail($id);

            // استرجاع الكميات من المخزون
            foreach ($purchase->items as $oldItem) {
                $product = Product::findOrFail($oldItem->product_id);
                $product->stock -= $oldItem->quantity;
                $product->save();
            }

            // حذف الأصناف القديمة
            PurchaseItem::where('purchase_id', $purchase->id)->delete();

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = $item['quantity'];
                $price   = $item['purchase_price'];

                $oldStock = $product->stock;
                $oldCost  = $product->purchase_price;

                $newStock = $oldStock + $qty;
                $avgCost  = ($oldStock * $oldCost + $qty * $price) / ($newStock ?: 1);

                $product->update([
                    'stock'          => $newStock,
                    'purchase_price' => $avgCost,
                ]);

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
                'supplier_id'     => $request->supplier_id,
                'notes'           => $request->notes,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'remaining_amount'=> $remaining,
            ]);

            // تسجيل الدفع الجديد إذا وجد
            if ($paidAmount > 0) {
                PurchasePayment::create([
                    'purchase_id'  => $purchase->id,
                    'amount'       => $paidAmount,
                    'payment_date' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', '✅ تم تحديث الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
        }
    }


    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();

        try {
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                $product->stock -= $item->quantity;
                $product->save();
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
            $purchase->load(['supplier', 'items.product', 'payments']);
            return view('admin.views.purchases.show', compact('purchase'));
        }

}
