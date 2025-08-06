<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $currentBranchId = session('current_branch_id');

        $sales = Sale::with('customer', 'branch')
            ->when($currentBranchId && $currentBranchId !== 'all', function ($query) use ($currentBranchId) {
                $query->where('branch_id', $currentBranchId);
            })
            ->latest()
            ->paginate(15);

        return view('admin.views.sales.index', compact('sales', 'currentBranchId'));
    }

    public function create()
    {
        $customers = Customer::all();
        $branches = Branch::all();

        // جلب الفرع الحالي من الجلسة
        $branch_id = session('current_branch_id');
        $branchProducts = [];

        if ($branch_id && $branch_id !== 'all') {
            $branch = Branch::with(['products' => function($q) {
                $q->with(['category', 'brand']);
            }])->find($branch_id);

            if ($branch) {
                $branchProducts = $branch->products->map(function($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->pivot->price,
                        'barcode' => $p->barcode,
                        'category_id' => $p->category_id,
                        'brand_id' => $p->brand_id,
                        'tax_included' => $p->pivot->is_tax_included ? 1 : 0,
                        'tax_percentage' => $p->pivot->tax_percentage,
                        'stock' => $p->pivot->stock,
                    ];
                })->values()->all();
            }
        }

        return view('admin.views.sales.create', compact('customers', 'branchProducts', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'initial_payment' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sale_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $total_profit = 0;

            $sale = new Sale();
            $sale->customer_id = $request->customer_id;
            $sale->customer_name = $request->customer_name;
            $sale->branch_id = session('current_branch_id');
            $sale->total = 0;
            $sale->profit = 0;
            $sale->discount = 0;
            $sale->paid = 0;
            $sale->remaining = 0;
            $sale->save();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $branch = Branch::with(['products' => function($q) use ($item) {
                    $q->where('products.id', $item['product_id']);
                }])->findOrFail(session('current_branch_id'));

                $branchProduct = $branch->products->first();

                if (!$branchProduct) {
                    return redirect()->back()->with('error', 'المنتج غير موجود في الفرع الحالي');
                }

                $taxRate = is_numeric($branchProduct->pivot->tax_percentage) ? $branchProduct->pivot->tax_percentage : 0;
                $quantity = $item['quantity'];
                $isTaxIncluded = $branchProduct->pivot->is_tax_included ? 1 : 0;
                // نستخدم سعر المنتج من pivot لو موجود، أو السعر من المنتج نفسه كبديل
                $base_price = $branchProduct->pivot->price ?? $product->sale_price;

                if ($isTaxIncluded) {
                    // السعر شامل الضريبة، نحسب السعر قبل الضريبة والضريبة
                    $base_price = $base_price / (1 + $taxRate / 100);
                    $taxValue = $base_price * $taxRate / 100;
                    $sale_price = $base_price + $taxValue;
                } else {
                    $taxValue = $base_price * $taxRate / 100;
                    $sale_price = $base_price + $taxValue;
                }

                $purchase_price = $product->purchase_price;
                $subtotal = $sale_price * $quantity;
                $profit = ($sale_price - $purchase_price) * $quantity;

                $total += $subtotal;
                $total_profit += $profit;

                \Log::info('Pivot Data:', [
                    'product_id' => $product->id,
                    'pivot_tax_percentage' => $branchProduct->pivot->tax_percentage,
                    'pivot_is_tax_included' => $branchProduct->pivot->is_tax_included,
                    'pivot_price' => $branchProduct->pivot->price,
                    'calculated_taxRate' => $taxRate,
                    'calculated_taxValue' => isset($taxValue) ? $taxValue : null,
                    'base_price' => $base_price,
                    'sale_price' => $sale_price,
                ]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'sale_price' => $sale_price,
                    'base_price' => $base_price,
                    'tax_value' => is_numeric($taxValue) ? $taxValue : 0,
                    'tax_percentage' => is_numeric($taxRate) ? $taxRate : 0,
                    'purchase_price' => $purchase_price,
                ]);

                $branch->products()->updateExistingPivot($item['product_id'], [
                    'stock' => $branchProduct->pivot->stock - $quantity
                ]);
            }

            $discount = $request->input('discount', 0);
            $finalTotal = $total - $discount;
            $initialPayment = $request->input('initial_payment', 0);

            $sale->total = $finalTotal;
            $sale->discount = $discount;
            $sale->profit = $total_profit;
            $sale->paid = $initialPayment;
            $sale->remaining = $finalTotal - $initialPayment;
            $sale->save();

            if ($initialPayment > 0 && $sale->customer_id) {
                CustomerPayment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $initialPayment,
                    'payment_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.sales.show', $sale->id)->with('success', 'تم إنشاء الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);

        $totalBeforeTax = $sale->saleItems->sum(fn($i) => $i->quantity * $i->base_price);
        $totalTax = $sale->saleItems->sum(fn($i) => $i->quantity * $i->tax_value);
        $totalAfterTax = $sale->saleItems->sum(fn($i) => $i->quantity * $i->sale_price);

        return view('admin.views.sales.show', compact('sale', 'totalBeforeTax', 'totalTax', 'totalAfterTax'));
    }

    public function edit($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);
        $products = Product::all();
        $customers = Customer::all();

        // جلب الفرع الحالي من السيشن
        $branch_id = session('current_branch_id');
        $branchProducts = [];

        if ($branch_id && $branch_id !== 'all') {
            $branch = Branch::with(['products' => function($q) {
                $q->with(['category', 'brand']);
            }])->find($branch_id);

            if ($branch) {
                $branchProducts = $branch->products->map(function($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->pivot->price,
                        'barcode' => $p->barcode,
                        'category_id' => $p->category_id,
                        'brand_id' => $p->brand_id,
                        'tax_included' => $p->pivot->is_tax_included ? 1 : 0,
                        'tax_percentage' => $p->pivot->tax_percentage,
                        'stock' => $p->pivot->stock,
                    ];
                })->values()->all();
            }
        }

        return view('admin.views.sales.edit', compact('sale', 'products', 'customers', 'branchProducts'))
            ->with([
                'initialPayment' => $sale->paid ?? 0,
                'remaining' => $sale->remaining ?? 0,
            ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'initial_payment' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sale_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::with('saleItems', 'customerPayments')->findOrFail($id);

            // استرجاع المخزون القديم
            $branch = Branch::find($sale->branch_id);
            foreach ($sale->saleItems as $oldItem) {
                if ($branch) {
                    $branchProduct = $branch->products()->where('products.id', $oldItem->product_id)->first();
                    if ($branchProduct) {
                        $branch->products()->updateExistingPivot($oldItem->product_id, [
                            'stock' => $branchProduct->pivot->stock + $oldItem->quantity
                        ]);
                    }
                }
            }
            $sale->saleItems()->delete();

            $total = 0;
            $total_profit = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $branch = Branch::with(['products' => function($q) use ($item) {
                    $q->where('products.id', $item['product_id']);
                }])->findOrFail($sale->branch_id);

                $branchProduct = $branch->products->first();

                if (!$branchProduct) {
                    return redirect()->back()->with('error', 'المنتج غير موجود في الفرع الحالي');
                }

                // تأكد أن القيم رقمية وليست null
                $taxRate = is_numeric($branchProduct->pivot->tax_percentage) ? floatval($branchProduct->pivot->tax_percentage) : 0;
                $quantity = $item['quantity'];
                $isTaxIncluded = $branchProduct->pivot->is_tax_included ? 1 : 0;
                $base_price = $branchProduct->pivot->price ?? $product->sale_price;

                if ($isTaxIncluded) {
                    $base_price = $base_price / (1 + $taxRate / 100);
                    $taxValue = $base_price * $taxRate / 100;
                    $sale_price = $base_price + $taxValue;
                } else {
                    $taxValue = $base_price * $taxRate / 100;
                    $sale_price = $base_price + $taxValue;
                }
                $purchase_price = $product->purchase_price;
                $subtotal = $sale_price * $quantity;
                $profit = ($sale_price - $purchase_price) * $quantity;

                $total += $subtotal;
                $total_profit += $profit;

                // هنا نضمن عدم حفظ null أبداً
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'sale_price' => $sale_price,
                    'base_price' => $base_price,
                    'tax_value' => is_numeric($taxValue) ? $taxValue : 0,
                    'tax_percentage' => is_numeric($taxRate) ? $taxRate : 0,
                    'purchase_price' => $purchase_price,
                ]);

                $branch->products()->updateExistingPivot($item['product_id'], [
                    'stock' => $branchProduct->pivot->stock - $quantity
                ]);
            }

            $discount = $request->input('discount', 0);
            $finalTotal = $total - $discount;
            $initialPayment = $request->input('initial_payment', 0);

            $sale->customer_id = $request->customer_id;
            $sale->customer_name = $request->customer_name;
            $sale->total = $finalTotal;
            $sale->discount = $discount;
            $sale->profit = $total_profit;
            $sale->paid = $initialPayment;
            $sale->remaining = $finalTotal - $initialPayment;
            $sale->save();

            // حذف الدفعات القديمة (اختياري حسب منطقك)
            $sale->customerPayments()->delete();

            if ($initialPayment > 0 && $sale->customer_id) {
                CustomerPayment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $initialPayment,
                    'payment_date' => now(),
                ]);
            }

            // إضافة دفعة جديدة (اختياري)
            $newPayment = floatval($request->input('new_payment', 0));
            if ($newPayment > 0 && $sale->customer_id) {
                CustomerPayment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $newPayment,
                    'payment_date' => now(),
                ]);
                $sale->paid += $newPayment;
                $sale->remaining -= $newPayment;
                $sale->save();
            }

            DB::commit();

            return redirect()->route('admin.sales.show', $sale->id)->with('success', 'تم تعديل الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();
        return redirect()->route('admin.sales.index')->with('success', 'تم حذف الفاتورة بنجاح.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('sales_ids', '[]'), true);
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $sale = Sale::find($id);
                if ($sale) {
                    $branch = Branch::find($sale->branch_id);
                    foreach ($sale->saleItems as $item) {
                        if ($branch) {
                            $branchProduct = $branch->products()->where('products.id', $item->product_id)->first();
                            if ($branchProduct) {
                                $branch->products()->updateExistingPivot($item->product_id, [
                                    'stock' => $branchProduct->pivot->stock + $item->quantity
                                ]);
                            }
                        }
                    }
                    $sale->saleItems()->delete();
                    $sale->customerPayments()->delete();
                    $sale->delete();
                }
            }
            return redirect()->route('admin.sales.index')->with('success', 'تم حذف الفواتير المحددة بنجاح.');
        }
        return back()->withErrors('لم يتم تحديد أي فاتورة.');
    }
}
