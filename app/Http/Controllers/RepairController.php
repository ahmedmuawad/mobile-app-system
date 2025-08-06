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

    public function index(Request $request)
{
    $branchId = $request->query('branch_id', session('current_branch_id', 'all'));

    // إذا جلب فرع مختلف عن اللي في الجلسة، يحدث الجلسة
    if ($branchId !== session('current_branch_id')) {
        session(['current_branch_id' => $branchId]);
    }

    $query = Repair::query();

    if ($branchId !== 'all') {
        $query->where('branch_id', $branchId);
    }

    $repairs = $query->with('customer')->paginate(15);
    $branches = Branch::all();

    return view('admin.views.repairs.index', compact('repairs', 'branches', 'branchId'));
}



public function create()
{
    $customers = Customer::all();
    $categories = Category::all();
    $branches = Branch::all();

    // جلب كل الفروع المرتبطة بالمنتجات (بدلاً من فرع واحد فقط)
    $products = Product::with('branches')->get();

    // تجهيز كميات المنتجات لكل فرع بشكل منظم
    $products->each(function ($product) {
        // مصفوفة branch_id => stock
        $product->branch_stock = $product->branches->pluck('pivot.stock', 'id');
    });

    return view('admin.views.repairs.create', compact('customers', 'categories', 'products', 'branches'));
}



public function store(Request $request)
{
    $request->validate([
        'customer_id' => 'nullable|exists:customers,id',
        'customer_name' => 'nullable|string|max:255',
        'device_type' => 'required|string|max:255',
        'problem_description' => 'required|string',
        'spare_part_id' => 'nullable|array',
        'spare_part_id.*' => 'exists:products,id',
        'quantities' => 'nullable|array',
        'quantities.*' => 'integer|min:1',
        'repair_cost' => 'required|numeric|min:0',
        'status' => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
        'discount' => 'nullable|numeric|min:0',
        'paid' => 'nullable|numeric|min:0',
        'branch_id' => 'required|exists:branches,id',
    ]);

    $branchId = $request->branch_id;
    $sparePartPrice = 0;

    $spareParts = collect();
    if ($request->filled('spare_part_id')) {
        $spareParts = Product::with(['branches' => function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }])->whereIn('id', $request->spare_part_id)->get();

        foreach ($spareParts as $sparePart) {
            $quantity = $request->quantities[$sparePart->id] ?? 1;
            $branchData = $sparePart->branches->first();

            if (!$branchData || $branchData->pivot->stock < $quantity) {
                return back()->with('error', "❌ لا يوجد كمية كافية من المنتج {$sparePart->name} في الفرع المختار.")->withInput();
            }

            // حساب السعر مع الضريبة حسب بيانات الفرع
            $price = $branchData->pivot->price;
            $isTaxIncluded = $branchData->pivot->is_tax_included;
            $tax = $branchData->pivot->tax_percentage ?? 0;

            $priceWithTax = $isTaxIncluded ? $price : $price * (1 + $tax / 100);
            $sparePartPrice += $priceWithTax * $quantity;
        }
    }

    $total = $sparePartPrice + $request->repair_cost - ($request->discount ?? 0);
    $total = max($total, 0);
    $paid = $request->paid ?? 0;

    if ($paid > $total) {
        return back()->with('error', '❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.')->withInput();
    }

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
        'branch_id' => $branchId,
    ]);

    if ($spareParts->isNotEmpty()) {
        foreach ($spareParts as $sparePart) {
            $quantity = $request->quantities[$sparePart->id] ?? 1;

            // ربط القطعة
            $repair->spareParts()->attach($sparePart->id, ['quantity' => $quantity]);

            // تحديث المخزون للفرع
            $branchData = $sparePart->branches->first();
            if ($branchData) {
                $newStock = max($branchData->pivot->stock - $quantity, 0);
                $sparePart->branches()->updateExistingPivot($branchId, ['stock' => $newStock]);
            }
        }
    }

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
        $repair = Repair::findOrFail($id);
        $customers = Customer::all();
        $categories = Category::all();
        $spareParts = Product::all();
        $branches = Branch::all();

        return view('admin.views.repairs.edit', compact('repair', 'customers', 'categories', 'spareParts', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $repair = Repair::findOrFail($id);

        // مثلاً لو تستخدم نظام الفروع
        $branchId = auth()->user()->branch_id ?? $request->branch_id ?? null;

        // تحقق من صلاحية الفرع (اختياري)
        if ($branchId && $repair->branch_id != $branchId) {
            abort(403, 'لا تملك صلاحية تعديل هذه الفاتورة');
        }

        // تحديث البيانات الأساسية للفاتورة
        $repair->customer_id = $request->customer_id;
        $repair->customer_name = $request->customer_name;
        $repair->device_type = $request->device_type;
        $repair->status = $request->status;
        $repair->problem_description = $request->problem_description;
        $repair->repair_type = $request->repair_type;
        $repair->repair_cost = $request->repair_cost;
        $repair->discount = $request->discount ?? 0;
        $repair->branch_id = $branchId; // تعيين الفرع إذا كان موجودًا

        // تحديث قطع الغيار (many-to-many مع كمية)
        $sparePartIds = $request->input('spare_part_ids', []);
        $quantities = $request->input('quantities', []);

        // بناء مصفوفة الربط مع الكميات لتحديث الـ pivot
        $syncData = [];
        foreach ($sparePartIds as $sparePartId) {
            $qty = isset($quantities[$sparePartId]) && $quantities[$sparePartId] > 0 ? (int)$quantities[$sparePartId] : 1;

            // تحديث متوسط سعر القطعة للفرع مع السعر الجديد (لو ترغب)
            $sparePart = SparePart::find($sparePartId);
            if ($sparePart) {
                // تحديث متوسط تكلفة القطعة في الفرع (مثلاً)
                $currentStock = $sparePart->branches()->where('branch_id', $branchId)->first();
                if ($currentStock) {
                    // متوسط التكلفة = (السعر الحالي * الكمية + سعر جديد * الكمية) / (الكمية الحالية + الكمية الجديدة)
                    $oldQty = $currentStock->pivot->quantity ?? 0;
                    $oldCost = $currentStock->pivot->cost ?? 0;
                    $newCost = $sparePart->sale_price;

                    $totalQty = $oldQty + $qty;
                    if ($totalQty > 0) {
                        $avgCost = (($oldCost * $oldQty) + ($newCost * $qty)) / $totalQty;
                    } else {
                        $avgCost = $newCost;
                    }

                    $sparePart->branches()->updateExistingPivot($branchId, [
                        'quantity' => $totalQty,
                        'cost' => $avgCost,
                    ]);
                }
            }

            $syncData[$sparePartId] = ['quantity' => $qty];
        }

        $repair->spareParts()->sync($syncData);

        // إعادة حساب الإجمالي (قطع الغيار + المصنعية - الخصم)
        $totalPartsCost = 0;
        foreach ($syncData as $sparePartId => $data) {
            $part = SparePart::find($sparePartId);
            if ($part) {
                $totalPartsCost += $part->sale_price * $data['quantity'];
            }
        }

        $total = $totalPartsCost + $repair->repair_cost - $repair->discount;
        $total = max(0, $total);

        $repair->total = $total;
        $repair->save();

        // تحديث المدفوعات محصورة، لكن لا تغير المدفوعات نفسها هنا
        // (إذا تريد دعم إضافة مدفوعات جديدة، فيجب إنشاء سجل جديد في جدول المدفوعات)

        return redirect()->route('admin.repairs.show', $repair->id)
            ->with('success', 'تم تحديث فاتورة الصيانة بنجاح');
    }



    public function destroy($id)
    {
        $repair = Repair::findOrFail($id);

        // إعادة الكميات إلى المخزون قبل حذف الفاتورة
        foreach ($repair->spareParts as $part) {
            $product = Product::find($part->id);
            if ($product) {
                $branchProduct = $product->branches()->where('branch_id', $repair->branch_id)->first();
                if ($branchProduct) {
                    $newStock = $branchProduct->pivot->stock + $part->pivot->quantity;
                    $product->branches()->updateExistingPivot($repair->branch_id, ['stock' => $newStock]);
                }
            }
        }

        $repair->delete();

        return redirect()->route('admin.repairs.index')->with('success', '🗑️ تم حذف الفاتورة بنجاح.');
    }

    public function show($id)
    {
        $repair = Repair::with(['spareParts', 'customer', 'payments'])->findOrFail($id);
        $globalSetting = Setting::first();

        return view('admin.views.repairs.show', [
            'repair' => $repair,
            'sparePart' => $repair->spareParts,
            'customer' => $repair->customer,
            'globalSetting' => $globalSetting,
        ]);
    }


public function showPaymentForm($id)
{
    $userBranchId = auth()->user()->branch_id ?? null;

    $query = Repair::with('payments')->where('id', $id);

    if ($userBranchId) {
        $query->where('branch_id', $userBranchId);
    }

    $repair = $query->firstOrFail();

    return view('admin.views.repairs.payment', compact('repair'));
}


public function updateStatus(Request $request)
{
    $request->validate([
        'repair_id' => 'required|exists:repairs,id',
        'delivery_status' => 'required|in:not_delivered,delivered,rejected',
        'paid_amount' => 'nullable|numeric|min:0',
    ]);

    $repair = Repair::with('payments')->findOrFail($request->repair_id);

    // احصل على الفرع من الفاتورة للإضافة على الدفعات والمصروفات
    $branchId = $repair->branch_id;

    // إضافة دفعة جديدة إذا تم إدخال مبلغ مدفوع
    if ($request->filled('paid_amount') && $request->paid_amount > 0) {
        $remaining = $repair->total - $repair->payments->sum('amount');
        $amount = min($request->paid_amount, $remaining);

        CustomerPayment::create([
            'repair_id' => $repair->id,
            'amount' => $amount,
            'payment_date' => now(),
            'branch_id' => $branchId,  // حفظ الفرع هنا
        ]);

        $newPaid = $repair->payments()->sum('amount') + $amount;
        $repair->paid = $newPaid;
        $repair->remaining = max($repair->total - $newPaid, 0);
    }

    $repair->delivery_status = $request->delivery_status;

    // تحديث حالة الفاتورة بناءً على حالة التسليم
    if ($request->delivery_status === 'delivered') {
        $repair->status = 'تم الإصلاح';
    } elseif ($request->delivery_status === 'rejected') {
        $repair->status = 'لم يتم الإصلاح';

        // في حالة الرفض، تسجيل مصروف استرجاع المبلغ إذا كان المدفوع > 0
        if ($repair->paid > 0) {
            Expense::create([
                'name' => 'استرجاع مبلغ',
                'amount' => $repair->paid,
                'description' => 'استرجاع مبلغ للعميل بسبب رفض الجهاز. رقم الفاتورة: ' . $repair->id,
                'expense_date' => now(),
                'expensable_id' => $repair->id,
                'expensable_type' => Repair::class,
                'branch_id' => $branchId, // حفظ الفرع هنا أيضًا
            ]);
        }

        $repair->paid = 0;
        $repair->remaining = 0;
    }

    $repair->save();

    return redirect()->route('admin.repairs.index')->with('success', 'تم تحديث حالة التسليم بنجاح.');
}


public function storePayment(Request $request, $repairId)
{
    $repair = Repair::with('payments')->findOrFail($repairId);

    // تحقق صلاحية الفرع (اختياري)
    $userBranchId = auth()->user()->branch_id ?? null;
    if ($userBranchId && $repair->branch_id != $userBranchId) {
        abort(403, 'لا تملك صلاحية إضافة دفعة لهذا الفرع');
    }

    $paidAmount = $repair->payments->sum('amount');
    $remaining = $repair->total - $paidAmount;

    $request->validate([
        'amount' => "required|numeric|min:0.01|max:$remaining",
    ]);

    CustomerPayment::create([
        'repair_id' => $repair->id,
        'amount' => $request->amount,
        'payment_date' => now(),
        'branch_id' => $repair->branch_id,  // حفظ فرع الفاتورة مع الدفعة
        'customer_id' => $repair->customer_id, // إضافة معرف العميل لو متوفر
    ]);

    $newPaid = $repair->payments()->sum('amount') + $request->amount;
    $repair->paid = $newPaid;
    $repair->remaining = max($repair->total - $newPaid, 0);
    $repair->save();

    return redirect()->route('admin.repairs.payments.create', $repair->id)
        ->with('success', 'تم تسجيل الدفعة بنجاح.');
}


}
