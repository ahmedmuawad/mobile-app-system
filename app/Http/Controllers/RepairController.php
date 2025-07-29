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
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    /**
     * عرض جميع فواتير الصيانة
     */
    public function index()
    {
        $repairs = Repair::with(['customer', 'spareParts'])->latest()->get();
        return view('admin.views.repairs.index', compact('repairs'));
    }

    /**
     * صفحة إنشاء فاتورة جديدة
     */
    public function create()
    {
        $customers  = Customer::all();
        $categories = Category::all();
        $products   = Product::select('id', 'name', 'sale_price', 'category_id', 'stock')->get();

        return view('admin.views.repairs.create', compact('customers', 'categories', 'products'));
    }

    /**
     * تخزين الفاتورة الجديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'          => 'nullable|exists:customers,id',
            'customer_name'        => 'nullable|string|max:255',
            'device_type'          => 'required|string|max:255',
            'problem_description'  => 'required|string',
            'spare_part_ids'       => 'nullable|array',
            'spare_part_ids.*'     => 'exists:products,id',
            'quantities'           => 'nullable|array',
            'repair_cost'          => 'required|numeric|min:0',
            'status'               => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
            'discount'             => 'nullable|numeric|min:0',
            'paid'                 => 'nullable|numeric|min:0',
            'device_condition'     => 'nullable|string',
            'repair_type'          => 'required|in:hardware,software,both',
        ]);

        DB::transaction(function () use ($request) {
            $sparePartsPrice = 0;
            $syncData = [];

            if ($request->has('spare_part_ids')) {
                $parts = Product::whereIn('id', $request->spare_part_ids)->get();

                foreach ($parts as $part) {
                    $qty = $request->quantities[$part->id] ?? 1;
                    if ($part->stock < $qty) {
                        throw new \Exception("❌ القطعة {$part->name} غير متوفرة بالكميات المطلوبة.");
                    }
                    $part->decrement('stock', $qty);
                    $sparePartsPrice += $part->sale_price * $qty;
                    $syncData[$part->id] = ['quantity' => $qty];
                }
            }

            $total = max(0, $sparePartsPrice + $request->repair_cost - ($request->discount ?? 0));
            $paid  = $request->paid ?? 0;

            if ($paid > $total) {
                throw new \Exception('❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.');
            }

            $repair = Repair::create([
                'customer_id'         => $request->customer_id,
                'customer_name'       => $request->customer_name,
                'device_type'         => $request->device_type,
                'problem_description' => $request->problem_description,
                'repair_cost'         => $request->repair_cost,
                'discount'            => $request->discount ?? 0,
                'total'               => $total,
                'status'              => $request->status,
                'paid'                => $paid,
                'remaining'           => $total - $paid,
                'device_condition'    => $request->device_condition,
                'repair_type'         => $request->repair_type,
            ]);

            if (!empty($syncData)) {
                $repair->spareParts()->sync($syncData);
            }

            if ($paid > 0) {
                $this->recordPayment($repair->id, $paid, 'سداد مبدئي لفاتورة صيانة');
            }
        });

        return redirect()->route('admin.repairs.index')->with('success', '✅ تم حفظ فاتورة الصيانة بنجاح.');
    }

    /**
     * صفحة تعديل الفاتورة
     */
    public function edit($id)
    {
        $repair = Repair::with(['spareParts', 'payments'])->findOrFail($id);
        $customers = Customer::all();
        $categories = Category::all();
        $spareParts = Product::all();

        return view('admin.views.repairs.edit', compact('repair', 'customers', 'categories', 'spareParts'));
    }

    /**
     * تحديث الفاتورة
     */
    public function update(Request $request, Repair $repair)
    {
        $request->validate([
            'customer_id'          => 'nullable|exists:customers,id',
            'customer_name'        => 'nullable|string|max:255',
            'device_type'          => 'required|string|max:255',
            'problem_description'  => 'required|string',
            'spare_part_ids'       => 'nullable|array',
            'spare_part_ids.*'     => 'exists:products,id',
            'quantities'           => 'nullable|array',
            'repair_cost'          => 'required|numeric|min:0',
            'status'               => 'required|in:جاري,تم الإصلاح,لم يتم الإصلاح',
            'discount'             => 'nullable|numeric|min:0',
            'paid'                 => 'nullable|numeric|min:0',
            'device_condition'     => 'nullable|string',
            'repair_type'          => 'required|in:hardware,software,both',
        ]);

        DB::transaction(function () use ($request, $repair) {
            $sparePartsPrice = 0;
            $syncData = [];

            if ($request->has('spare_part_ids')) {
                $parts = Product::whereIn('id', $request->spare_part_ids)->get();

                foreach ($parts as $part) {
                    $qty = $request->quantities[$part->id] ?? 1;
                    if ($part->stock < $qty) {
                        throw new \Exception("❌ القطعة {$part->name} غير متوفرة بالكميات المطلوبة.");
                    }
                    $part->decrement('stock', $qty);
                    $sparePartsPrice += $part->sale_price * $qty;
                    $syncData[$part->id] = ['quantity' => $qty];
                }
            }

            $total = $request->total ?? max(0, $sparePartsPrice + $request->repair_cost - ($request->discount ?? 0));
            $paid  = $request->paid ?? 0;

            if ($paid > $total) {
                throw new \Exception('❌ المبلغ المدفوع يتجاوز إجمالي الفاتورة.');
            }

            $repair->update([
                'customer_id'         => $request->customer_id,
                'customer_name'       => $request->customer_name,
                'device_type'         => $request->device_type,
                'problem_description' => $request->problem_description,
                'repair_cost'         => $request->repair_cost,
                'discount'            => $request->discount ?? 0,
                'total'               => $total,
                'status'              => $request->status,
                'paid'                => $paid,
                'remaining'           => $total - $paid,
                'device_condition'    => $request->device_condition,
                'repair_type'         => $request->repair_type,
            ]);

            if (!empty($syncData)) {
                $repair->spareParts()->sync($syncData);
            }
        });

        return redirect()->route('admin.repairs.index')->with('success', '✅ تم تحديث الفاتورة بنجاح.');
    }

    /**
     * حذف الفاتورة
     */
    public function destroy($id)
    {
        Repair::findOrFail($id)->delete();
        return redirect()->route('admin.repairs.index')->with('success', '🗑️ تم حذف الفاتورة بنجاح.');
    }

    /**
     * عرض تفاصيل الفاتورة
     */
    public function show($id)
    {
        $repair = Repair::with(['spareParts', 'customer', 'payments'])->findOrFail($id);
        $globalSetting = Setting::first();

        return view('admin.views.repairs.show', compact('repair', 'globalSetting'));
    }

    /**
     * جلب المنتجات حسب الفئة (AJAX)
     */
    public function getProductsByCategory($categoryId)
    {
        return response()->json(Product::where('category_id', $categoryId)->where('stock', '>', 0)->get(['id', 'name', 'sale_price']));
    }

    /**
     * نموذج دفع جديد
     */
    public function showPaymentForm($id)
    {
        $repair = Repair::with('payments')->findOrFail($id);
        return view('admin.views.repairs.payment', compact('repair'));
    }

    /**
     * تخزين دفعة جديدة
     */
    public function storePayment(Request $request, $id)
    {
        $repair = Repair::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . ($repair->remaining),
        ]);

        DB::transaction(function () use ($repair, $request) {
            $this->recordPayment($repair->id, $request->amount, 'سداد مستحق من العميل لفاتورة صيانة');
            $repair->increment('paid', $request->amount);
            $repair->decrement('remaining', $request->amount);
        });

        return redirect()->route('admin.repairs.index')->with('success', '✅ تم تسجيل السداد بنجاح.');
    }

    /**
     * تسجيل الدفع (إيراد فقط)
     */
    private function recordPayment($repairId, $amount, $description)
    {
        CustomerPayment::create([
            'repair_id'    => $repairId,
            'amount'       => $amount,
            'payment_date' => now(),
        ]);
    }
}
