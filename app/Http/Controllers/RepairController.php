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
use App\Services\StockService;

class RepairController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $branchId = $request->query('branch_id', session('current_branch_id', 'all'));

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
        $products = Product::with('branches')->get();

        $products->each(function ($product) {
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

                $repair->spareParts()->attach($sparePart->id, ['quantity' => $quantity]);

                $this->stockService->decreaseStock($sparePart->id, $branchId, $quantity, 'خصم لقطع غيار صيانة #' . $repair->id);
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
        $repair = Repair::with('spareParts')->findOrFail($id);
        $customers = Customer::all();
        $categories = Category::all();
        $branches = Branch::all();
        $products = Product::with('branches')->get();

        return view('admin.views.repairs.edit', compact('repair', 'customers', 'categories', 'products', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $repair = Repair::with('spareParts')->findOrFail($id);
        $branchId = $repair->branch_id;

        // إعادة الكميات القديمة للمخزون
        foreach ($repair->spareParts as $part) {
            $this->stockService->increaseStock($part->id, $branchId, $part->pivot->quantity, 'إرجاع قطع غيار من تعديل فاتورة صيانة #' . $repair->id);
        }

        // مسح القطع القديمة
        $repair->spareParts()->detach();

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

                $price = $branchData->pivot->price;
                $isTaxIncluded = $branchData->pivot->is_tax_included;
                $tax = $branchData->pivot->tax_percentage ?? 0;

                $priceWithTax = $isTaxIncluded ? $price : $price * (1 + $tax / 100);
                $sparePartPrice += $priceWithTax * $quantity;

                $repair->spareParts()->attach($sparePart->id, ['quantity' => $quantity]);

                $this->stockService->decreaseStock($sparePart->id, $branchId, $quantity, 'خصم لقطع غيار صيانة #' . $repair->id);
            }
        }

        $total = $sparePartPrice + $request->repair_cost - ($request->discount ?? 0);
        $total = max($total, 0);

        $repair->update([
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer_name,
            'device_type' => $request->device_type,
            'problem_description' => $request->problem_description,
            'repair_cost' => $request->repair_cost,
            'discount' => $request->discount ?? 0,
            'total' => $total,
            'status' => $request->status,
            'remaining' => $total - $repair->paid,
        ]);

        return redirect()->route('admin.repairs.show', $repair->id)->with('success', '✅ تم تحديث فاتورة الصيانة بنجاح.');
    }

    public function destroy($id)
    {
        $repair = Repair::with('spareParts')->findOrFail($id);

        foreach ($repair->spareParts as $part) {
            $this->stockService->increaseStock($part->id, $repair->branch_id, $part->pivot->quantity, 'إرجاع قطع غيار من حذف فاتورة صيانة #' . $repair->id);
        }

        $repair->delete();

        return redirect()->route('admin.repairs.index')->with('success', '🗑️ تم حذف الفاتورة وإرجاع القطع للمخزون.');
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
        $repair = Repair::with('payments')->findOrFail($id);
        return view('admin.views.repairs.payment', compact('repair'));
    }

public function updateStatus(Request $request)
{
    $request->validate([
        'repair_id' => 'required|exists:repairs,id',
        'delivery_status' => 'required|in:not_delivered,delivered,rejected',
        'paid_amount' => 'nullable|numeric|min:0',
    ]);

    $repair = Repair::with('payments', 'spareParts')->findOrFail($request->repair_id);
    $branchId = $repair->branch_id;

    // إضافة دفعة جديدة إذا تم إدخال مبلغ مدفوع
    if ($request->filled('paid_amount') && $request->paid_amount > 0) {
        $remaining = $repair->total - $repair->payments->sum('amount');
        $amount = min($request->paid_amount, $remaining);

        CustomerPayment::create([
            'repair_id' => $repair->id,
            'amount' => $amount,
            'payment_date' => now(),
            'branch_id' => $branchId,
        ]);

        $newPaid = $repair->payments()->sum('amount') + $amount;
        $repair->paid = $newPaid;
        $repair->remaining = max($repair->total - $newPaid, 0);
    }

    $repair->delivery_status = $request->delivery_status;

    if ($request->delivery_status === 'delivered') {
        $repair->status = 'تم الإصلاح';
    } elseif ($request->delivery_status === 'rejected') {
        $repair->status = 'لم يتم الإصلاح';

        // إرجاع كل قطع الغيار للمخزون
        foreach ($repair->spareParts as $part) {
            $this->stockService->increaseStock(
                $part->id,
                $branchId,
                $part->pivot->quantity,
                'إرجاع قطع غيار بسبب رفض الجهاز في فاتورة صيانة #' . $repair->id
            );
        }

        // تسجيل مصروف استرجاع المبلغ إذا كان فيه مدفوع
        if ($repair->paid > 0) {
            Expense::create([
                'name' => 'استرجاع مبلغ',
                'amount' => $repair->paid,
                'description' => 'استرجاع مبلغ للعميل بسبب رفض الجهاز. رقم الفاتورة: ' . $repair->id,
                'expense_date' => now(),
                'expensable_id' => $repair->id,
                'expensable_type' => Repair::class,
                'branch_id' => $branchId,
            ]);
        }

        // تصفير المدفوع والمتبقي
        $repair->paid = 0;
        $repair->remaining = 0;
    }

    $repair->save();

    return redirect()->route('admin.repairs.index')->with('success', 'تم تحديث حالة التسليم بنجاح.');
}


    public function storePayment(Request $request, $repairId)
    {
        $repair = Repair::with('payments')->findOrFail($repairId);
        $paidAmount = $repair->payments->sum('amount');
        $remaining = $repair->total - $paidAmount;

        $request->validate([
            'amount' => "required|numeric|min:0.01|max:$remaining",
        ]);

        CustomerPayment::create([
            'repair_id' => $repair->id,
            'amount' => $request->amount,
            'payment_date' => now(),
            'branch_id' => $repair->branch_id,
            'customer_id' => $repair->customer_id,
        ]);

        $newPaid = $repair->payments()->sum('amount') + $request->amount;
        $repair->paid = $newPaid;
        $repair->remaining = max($repair->total - $newPaid, 0);
        $repair->save();

        return redirect()->route('admin.repairs.payments.create', $repair->id)->with('success', 'تم تسجيل الدفعة بنجاح.');
    }
}
