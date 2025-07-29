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

class RepairController extends Controller
{
    public function index()
    {
        $repairs = Repair::with('customer', 'sparePart')->latest()->get();
        return view('admin.views.repairs.index', compact('repairs'));
    }

    public function create()
    {
        $customers = Customer::all();
        $categories = Category::all();
        return view('admin.views.repairs.create', compact('customers', 'categories'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'customer_id'          => 'nullable|exists:customers,id',
            'customer_name'        => 'nullable|string|max:255',
            'device_type'          => 'required|string|max:255',
            'problem_description'  => 'required|string',
            'spare_part_id'        => 'nullable|exists:products,id',
            'repair_cost'          => 'required|numeric|min:0',
            'status'               => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
            'discount'             => 'nullable|numeric|min:0',
            'paid'                => 'nullable|numeric|min:0',

        ]);

        $sparePartPrice = 0;

        if ($request->spare_part_id) {
            $sparePart = Product::find($request->spare_part_id);

            if (!$sparePart || $sparePart->stock < 1) {
                return back()->with('error', '❌ الكمية غير كافية من قطعة الغيار المختارة.')->withInput();
            }

            $sparePart->stock -= 1;
            $sparePart->save();

            $sparePartPrice = $sparePart->sale_price;
        }

        $total = $sparePartPrice + $request->repair_cost - ($request->discount ?? 0);
        if ($total < 0) $total = 0;

        $paid = $request->paid ?? 0;
        if ($paid > $total) {
            return back()->with('error', '❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.')->withInput();
        }
    $paid = $request->paid ?? 0;
        $repair = new Repair();
        $repair->customer_id         = $request->customer_id;
        $repair->customer_name       = $request->customer_name;
        $repair->device_type         = $request->device_type;
        $repair->problem_description = $request->problem_description;
        $repair->spare_part_id       = $request->spare_part_id;
        $repair->repair_cost         = $request->repair_cost;
        $repair->discount            = $request->discount ?? 0;
        $repair->total               = $total;
        $repair->status              = $request->status;
        $repair->paid                = $paid;                  // ✅ أضف هذا
        $repair->remaining           = $total - $paid;         // ✅ أضف هذا
        $repair->save();

        if ($paid > 0) {
            CustomerPayment::create([
                'repair_id'    => $repair->id,
                'amount'       => $paid,
                'payment_date' => now(),
            ]);

            Expense::create([
                'name'        => 'دفعة صيانة #' . $repair->id,
                'amount'      => $paid,
                'description' => 'سداد مبدئي لفاتورة صيانة #' . $repair->id,
                'date'        => now(),
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

        return view('admin.views.repairs.edit', compact('repair', 'customers', 'categories', 'spareParts'));
    }

public function update(Request $request, $id)
{
    $request->validate([
        'customer_id'          => 'nullable|exists:customers,id',
        'customer_name'        => 'nullable|string|max:255',
        'device_type'          => 'required|string|max:255',
        'problem_description'  => 'required|string',
        'spare_part_id'        => 'nullable|exists:products,id',
        'repair_cost'          => 'required|numeric|min:0',
        'status'               => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
        'discount'             => 'nullable|numeric|min:0',
        'paid'                 => 'nullable|numeric|min:0',
    ]);

    $repair = Repair::findOrFail($id);

    $sparePartPrice = 0;
    if ($request->spare_part_id) {
        $sparePart = Product::find($request->spare_part_id);
        $sparePartPrice = $sparePart?->sale_price ?? 0;
    }

    $total = $sparePartPrice + $request->repair_cost - ($request->discount ?? 0);
    if ($total < 0) $total = 0;

    $paid = $request->paid ?? 0;
    if ($paid > $total) {
        return back()->with('error', '❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.')->withInput();
    }
    $paid = $request->paid ?? 0;

    $repair->customer_id         = $request->customer_id;
    $repair->customer_name       = $request->customer_name;
    $repair->device_type         = $request->device_type;
    $repair->problem_description = $request->problem_description;
    $repair->spare_part_id       = $request->spare_part_id;
    $repair->repair_cost         = $request->repair_cost;
    $repair->discount            = $request->discount ?? 0;
    $repair->total               = $total;
    $repair->status              = $request->status;
    $repair->paid                = $paid;
    $repair->remaining           = $total - $paid;

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
        $repair = Repair::with(['sparePart', 'customer', 'payments'])->findOrFail($id);
        $globalSetting = Setting::first();

        return view('admin.views.repairs.show', [
            'repair'        => $repair,
            'sparePart'     => $repair->sparePart,
            'customer'      => $repair->customer,
            'globalSetting' => $globalSetting
        ]);
    }

    public function getProductsByCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)->get(['id', 'name', 'sale_price']);
        return response()->json($products);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function showPaymentForm($id)
    {
        $repair = Repair::with('payments')->findOrFail($id);
        return view('admin.views.repairs.payment', compact('repair'));
    }

    public function storePayment(Request $request, $id)
    {
        $repair = Repair::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . ($repair->total - $repair->payments->sum('amount')),
        ]);

        CustomerPayment::create([
            'repair_id'    => $repair->id,
            'amount'       => $request->amount,
            'payment_date' => now(),
        ]);

        Expense::create([
            'name'        => 'دفعة صيانة #' . $repair->id,
            'amount'      => $request->amount,
            'description' => 'سداد مستحق من العميل لفاتورة صيانة #' . $repair->id,
            'date'        => now(),
        ]);

        return redirect()->route('admin.repairs.index')->with('success', '✅ تم تسجيل السداد بنجاح.');
    }
}
