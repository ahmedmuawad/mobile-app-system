<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Repair;
use App\Models\Setting;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\Branch;

class RepairController extends Controller
{
    public function index()
        {
            $repairs = Repair::with(['customer', 'spareParts'])->latest()->get();
            return view('admin.views.repairs.index', compact('repairs'));
        }

    public function create()
        {
            $customers = Customer::all();
            $categories = Category::all();
            $products = Product::all();
            $branches = Branch::all(); // أضف هذا السطر
            return view('admin.views.repairs.create', compact('customers', 'categories', 'products', 'branches'));
        }

public function store(Request $request)
{
    // تحقق من المدخلات
    $request->validate([
        'customer_id' => 'nullable|exists:customers,id',
        'customer_name' => 'nullable|string|max:255',
        'device_type' => 'required|string|max:255',
        'problem_description' => 'required|string',
        'spare_part_id' => 'nullable|exists:products,id',
        'repair_cost' => 'required|numeric|min:0',
        'status' => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
        'discount' => 'nullable|numeric|min:0',
        'paid' => 'nullable|numeric|min:0',
        'branch_id' => 'required|exists:branches,id',
    ]);

    // حساب أسعار قطع الغيار المحددة
    $sparePartPrice = 0;
    if ($request->spare_part_id) {
        $spareParts = Product::whereIn('id', $request->spare_part_id)->get();
        foreach ($spareParts as $sparePart) {
            $sparePartPrice += $sparePart->sale_price * ($request->quantities[$sparePart->id] ?? 1);
        }
    }

    // حساب الإجمالي
    $total = $sparePartPrice + $request->repair_cost - ($request->discount ?? 0);
    $total = max($total, 0);
    $paid = $request->paid ?? 0;

    // التحقق من المدفوعات
    if ($paid > $total) {
        return back()->with('error', '❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.')->withInput();
    }

    // إنشاء الفاتورة
    $repair = Repair::create([
        'customer_id' => $request->customer_id,
        'customer_name' => $request->customer_name,
        'device_type' => $request->device_type,
        'problem_description' => $request->problem_description,
        'repair_cost' => $request->repair_cost,
        'discount' => $request->discount ?? 0,
        'total' => $total,
        'status' => $request->status,
        'paid' => $paid,
        'remaining' => $total - $paid,
        'delivery_status' => 'not_delivered',
        'branch_id' => $request->branch_id,
    ]);

    // إضافة قطع الغيار مع الكميات إلى جدول الربط
    if ($request->has('spare_part_id')) {
        foreach ($request->spare_part_id as $index => $sparePartId) {
            $quantity = $request->quantities[$sparePartId] ?? 1;
            $repair->spareParts()->attach($sparePartId, ['quantity' => $quantity]);
            // خصم الكمية من المخزون
            $product = Product::find($sparePartId);
            if ($product) {
                $product->decrement('stock', $quantity);
            }
        }
    }

    // تسجيل المدفوعات
    if ($paid > 0) {
        CustomerPayment::create([
            'repair_id' => $repair->id,
            'amount' => $paid,
            'payment_date' => now(),
        ]);
    }

    return redirect()->route('admin.repairs.index')->with('success', '✅ تم حفظ فاتورة الصيانة بنجاح.');
}


    public function edit($id)
        {
            $repair     = Repair::findOrFail($id);
            $customers  = Customer::all();
            $categories = Category::all();
            $spareParts = Product::all();
            $branches = Branch::all();

            return view('admin.views.repairs.edit', compact('repair', 'customers', 'categories', 'spareParts', 'branches'));
        }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id'         => 'nullable|exists:customers,id',
            'customer_name'       => 'nullable|string|max:255',
            'device_type'         => 'required|string|max:255',
            'problem_description' => 'required|string',
            'spare_part_id'       => 'nullable|exists:products,id',
            'repair_cost'         => 'required|numeric|min:0',
            'status'              => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
            'discount'            => 'nullable|numeric|min:0',
            'paid'                => 'nullable|numeric|min:0',
            'branch_id'           => 'required|exists:branches,id',
        ]);

        $repair = Repair::findOrFail($id);

        // 1. إعادة الكميات القديمة للمخزون
        foreach ($repair->spareParts as $oldPart) {
            $product = Product::find($oldPart->id);
            if ($product) {
                $product->increment('stock', $oldPart->pivot->quantity);
            }
        }
        // 2. حذف الربط القديم
        $repair->spareParts()->detach();

        // 3. حساب إجمالي قطع الغيار الجديد وضبط الربط وخصم الاستوك
        $sparePartPrice = 0;
        if ($request->spare_part_id) {
            foreach ($request->spare_part_id as $sparePartId) {
                $quantity = $request->quantities[$sparePartId] ?? 1;
                $product = Product::find($sparePartId);
                if ($product) {
                    $sparePartPrice += $product->sale_price * $quantity;
                    $repair->spareParts()->attach($sparePartId, ['quantity' => $quantity]);
                    $product->decrement('stock', $quantity);
                }
            }
        }

        // 4. حساب الإجمالي
        $total = $sparePartPrice + $request->repair_cost - ($request->discount ?? 0);
        $total = max($total, 0);
        $paid = $request->paid ?? 0;

        if ($paid > $total) {
            return back()->with('error', '❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.')->withInput();
        }

        // 5. تحديث بيانات الفاتورة
        $repair->customer_id         = $request->customer_id;
        $repair->customer_name       = $request->customer_name;
        $repair->device_type         = $request->device_type;
        $repair->problem_description = $request->problem_description;
        $repair->repair_cost         = $request->repair_cost;
        $repair->discount            = $request->discount ?? 0;
        $repair->total               = $total;
        $repair->status              = $request->status;
        $repair->paid                = $paid;
        $repair->remaining           = $total - $paid;
        $repair->branch_id           = $request->branch_id;
        $repair->save();

        return redirect()->route('admin.repairs.index')->with('success', '✅ تم تحديث الفاتورة بنجاح.');
    }

    public function destroy($id)
        {
            $repair = Repair::findOrFail($id);
            $repair->delete();

            return redirect()->route('admin.repairs.index')->with('success', '🗑️ تم حذف الفاتورة بنجاح.');
        }

    public function show($id)
        {
            $repair = Repair::with(['spareParts', 'customer', 'payments'])->findOrFail($id);
            $globalSetting = Setting::first();

            return view('admin.views.repairs.show', [
                'repair'        => $repair,
                'sparePart'     => $repair->spareParts,
                'customer'      => $repair->customer,
                'globalSetting' => $globalSetting
            ]);
        }

    public function getProductsByCategory($categoryId)
        {
            $products = Product::where('category_id', $categoryId)->get(['id', 'name', 'sale_price']);
            return response()->json($products);
        }

    public function showPaymentForm($id)
        {
            $repair = Repair::with('payments')->findOrFail($id);
            return view('admin.views.repairs.payment', compact('repair'));
        }

    public function updateStatus(Request $request)
        {
            $request->validate([
                'repair_id'       => 'required|exists:repairs,id',
                'delivery_status' => 'required|in:not_delivered,delivered,rejected',
                'paid_amount'     => 'nullable|numeric|min:0',
            ]);

            $repair = Repair::with('payments')->findOrFail($request->repair_id);

            // إذا تم إدخال دفعة جديدة
            if ($request->filled('paid_amount') && $request->paid_amount > 0) {
                // لا تتجاوز الدفعة المتبقي
                $remaining = $repair->total - $repair->payments->sum('amount');
                $amount = min($request->paid_amount, $remaining);

                // سجل الدفعة
                CustomerPayment::create([
                    'repair_id'    => $repair->id,
                    'amount'       => $amount,
                    'payment_date' => now(),
                ]);

                // حدث paid و remaining
                $newPaid = $repair->payments()->sum('amount') + $amount;
                $repair->paid = $newPaid;
                $repair->remaining = max($repair->total - $newPaid, 0);
            }

            // تحديث حالة التسليم
            $repair->delivery_status = $request->delivery_status;
            $repair->save();

            if ($request->delivery_status === 'delivered') {
                $repair->status = 'تم الإصلاح';
            } elseif ($request->delivery_status === 'rejected') {
                $repair->status = 'لم يتم الإصلاح';

                // سجل مصروف استرجاع المبلغ
                $paidAmount = $repair->paid;
                if ($paidAmount > 0) {
                    Expense::create([
                        'name'             => 'استرجاع مبلغ',
                        'amount'           => $paidAmount,
                        'description'      => 'استرجاع مبلغ للعميل بسبب رفض الجهاز. رقم الفاتورة: ' . $repair->id,
                        'expense_date'     => now(), // أضف هذا السطر
                        'expensable_id'    => $repair->id,
                        'expensable_type'  => Repair::class,
                    ]);
                }

                // تصفير المدفوع والمتبقي
                $repair->paid = 0;
                $repair->remaining = 0;
                $repair->save();
            }

            return redirect()->route('admin.repairs.index')->with('success', 'تم تحديث حالة التسليم بنجاح.');
        }

    public function rejectAndRefund()
        {
            $paidAmount = $this->paid;
            $this->paid = 0;
            $this->remaining = 0;
            $this->save();

            Expense::create([
                'name' => 'استرجاع مبلغ',
                'amount' => $paidAmount,
                'description' => 'استرجاع مبلغ للعميل بسبب رفض الجهاز. رقم الفاتورة: ' . $repair->id,
                'expense_date' => now(),
                'expensable_id' => $this->id,
                'expensable_type' => Repair::class,
            ]);
        }
public function storePayment(Request $request, $repairId)
{
    $repair = Repair::with('payments')->findOrFail($repairId);

    $paidAmount = $repair->payments->sum('amount');
    $remaining = $repair->total - $paidAmount;

    $request->validate([
        'amount' => "required|numeric|min:0.01|max:$remaining",
    ]);

    // سجل الدفعة
    CustomerPayment::create([
        'repair_id'    => $repair->id,
        'amount'       => $request->amount,
        'payment_date' => now(),
    ]);

    // تحديث paid و remaining في repair بناءً على مجموع الدفعات
    $newPaid = $repair->payments()->sum('amount') + $request->amount;
    $repair->paid = $newPaid;
    $repair->remaining = max($repair->total - $newPaid, 0);
    $repair->save();

    return redirect()->route('admin.repairs.payments.create', $repair->id)
        ->with('success', 'تم تسجيل الدفعة بنجاح.');
}
}
