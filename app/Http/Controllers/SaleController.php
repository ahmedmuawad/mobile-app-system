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
        $sales = Sale::with('customer')->latest()->paginate(15);
        return view('admin.views.sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::with(['category', 'brand'])->get();
        $branches = Branch::with(['products' => function ($q) {
            $q->with('category', 'brand');
        }])->get();

        $branchProducts = $branches->mapWithKeys(function($b) {
            return [
                $b->id => $b->products->map(function($p) {
                    $pivot = $p->pivot;
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $pivot->price,
                        'barcode' => $p->barcode,
                        'category_id' => $p->category_id,
                        'brand_id' => $p->brand_id,
                        'tax_included' => $pivot->is_tax_included !== null ? (int) $pivot->is_tax_included : (int) $p->is_tax_included,
                        'tax_percentage' => $pivot->tax_percentage ?? $p->tax_percentage ?? 0,
                        'stock' => $pivot->stock,
                    ];
                })->values()
            ];
        });

        return view('admin.views.sales.create', compact('customers', 'products', 'branches', 'branchProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
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
            $branch = Branch::findOrFail($request->branch_id);
            $total = 0;
            $total_profit = 0;

            $sale = new Sale();
            $sale->branch_id = $branch->id;
            if ($request->filled('customer_id')) {
                $sale->customer_id = $request->customer_id;
                $sale->customer_name = null;
            } elseif ($request->filled('customer_name')) {
                $sale->customer_id = null;
                $sale->customer_name = $request->customer_name;
            } else {
                return back()->withErrors('يرجى إدخال اسم العميل أو اختياره من القائمة.')->withInput();
            }
            $sale->total = $sale->profit = $sale->discount = $sale->paid = $sale->remaining = 0;
            $sale->save();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $branchProduct = $branch->products()->where('products.id', $product->id)->first();

                if (!$branchProduct || $branchProduct->pivot->stock < $item['quantity']) {
                    DB::rollBack();
                    return back()->withErrors('الكمية غير متوفرة في الفرع المختار.')->withInput();
                }

                $sale_price = $branchProduct->pivot->price;
                $purchase_price = $branchProduct->pivot->purchase_price ?? $product->purchase_price;
                $taxRate = $branchProduct->pivot->tax_percentage ?? $product->tax_percentage ?? 0;
                $isTaxIncluded = $branchProduct->pivot->is_tax_included ?? $product->is_tax_included;

                $finalPrice = $isTaxIncluded ? $sale_price : $sale_price + ($sale_price * ($taxRate / 100));
                $quantity = $item['quantity'];
                $subtotal = $finalPrice * $quantity;
                $profit = ($sale_price - $purchase_price) * $quantity;

                $total += $subtotal;
                $total_profit += $profit;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'sale_price' => $finalPrice,
                    'base_price' => $sale_price,
                    'purchase_price' => $purchase_price,
                ]);

                $branch->products()->updateExistingPivot($product->id, [
                    'stock' => $branchProduct->pivot->stock - $quantity
                ]);
            }

            $discount = $request->input('discount', 0);
            $finalTotal = $total - $discount;
            $initialPayment = $request->input('initial_payment', 0);

            $sale->total = $finalTotal;
            $sale->discount = $discount;
            $sale->profit = $total_profit;
            $sale->paid = $initialPayment > 0 ? $initialPayment : 0;
            $sale->remaining = $finalTotal - $sale->paid;
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
        $totalTax = $sale->saleItems->sum(fn($i) => $i->quantity * ($i->sale_price - $i->base_price));
        $totalAfterTax = $sale->saleItems->sum(fn($i) => $i->quantity * $i->sale_price);

        return view('admin.views.sales.show', compact('sale', 'totalBeforeTax', 'totalTax', 'totalAfterTax'));
    }

    public function edit($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);
        $products = Product::all();
        $customers = Customer::all();
        $branches = Branch::with(['products'])->get();

        $branchProducts = $branches->mapWithKeys(function($b) {
            return [
                $b->id => $b->products->map(function($p) {
                    $pivot = $p->pivot;
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $pivot->price,
                        'barcode' => $p->barcode,
                        'category_id' => $p->category_id,
                        'brand_id' => $p->brand_id,
                        'tax_included' => $pivot->is_tax_included !== null ? (int) $pivot->is_tax_included : (int) $p->is_tax_included,
                        'tax_percentage' => $pivot->tax_percentage ?? $p->tax_percentage ?? 0,
                        'stock' => $pivot->stock,
                    ];
                })->values()
            ];
        });

        return view('admin.views.sales.edit', compact('sale', 'products', 'customers', 'branches', 'branchProducts'))
            ->with([
                'initialPayment' => $sale->paid ?? 0,
                'remaining' => $sale->remaining ?? 0,
            ]);
    }

    public function update(Request $request, $id)
    {
        $hasItems = $request->has('items');
        $hasNewPayment = $request->filled('new_payment');

        if (!$hasItems && !$hasNewPayment) {
            return back()->withErrors(['error' => 'لا توجد بيانات لتعديل الفاتورة أو سداد دفعة.'])->withInput();
        }

        if ($hasItems) {
            $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'nullable|string|max:255',
                'discount' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.sale_price' => 'required|numeric|min:0',
            ]);
        }

        DB::beginTransaction();

        try {
            $sale = Sale::with('saleItems', 'customerPayments')->findOrFail($id);
            $branch = Branch::findOrFail($sale->branch_id);

            $finalTotal = $sale->total;
            $discount = $sale->discount;
            $profit = $sale->profit;

            if ($hasItems) {
                if (!$request->filled('customer_id') && !$request->filled('customer_name')) {
                    return back()->withErrors('يرجى إدخال اسم العميل أو اختياره من القائمة.')->withInput();
                }

                $sale->customer_id = $request->customer_id;
                $sale->customer_name = $request->customer_name;

                $total = 0;
                $profit = 0;

                foreach ($sale->saleItems as $oldItem) {
                    $branchProduct = $branch->products()->where('products.id', $oldItem->product_id)->first();
                    if ($branchProduct) {
                        $branch->products()->updateExistingPivot($oldItem->product_id, [
                            'stock' => $branchProduct->pivot->stock + $oldItem->quantity
                        ]);
                    }
                }
                $sale->saleItems()->delete();

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $branchProduct = $branch->products()->where('products.id', $product->id)->first();

                    if (!$branchProduct || $branchProduct->pivot->stock < $item['quantity']) {
                        DB::rollBack();
                        return back()->withErrors('الكمية غير متوفرة في الفرع المختار.')->withInput();
                    }

                    $sale_price = $branchProduct->pivot->price;
                    $purchase_price = $branchProduct->pivot->purchase_price ?? $product->purchase_price;
                    $taxRate = $branchProduct->pivot->tax_percentage ?? $product->tax_percentage ?? 0;
                    $isTaxIncluded = $branchProduct->pivot->is_tax_included ?? $product->is_tax_included;

                    $finalPrice = $isTaxIncluded ? $sale_price : $sale_price + ($sale_price * ($taxRate / 100));
                    $quantity = $item['quantity'];
                    $subtotal = $finalPrice * $quantity;
                    $profit += ($sale_price - $purchase_price) * $quantity;
                    $total += $subtotal;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'sale_price' => $finalPrice,
                        'purchase_price' => $purchase_price,
                        'base_price' => $sale_price,
                    ]);

                    $branch->products()->updateExistingPivot($product->id, [
                        'stock' => $branchProduct->pivot->stock - $quantity
                    ]);
                }

                $discount = $request->input('discount', 0);
                $finalTotal = $total - $discount;
            }

            $sale->total = $finalTotal;
            $sale->discount = $discount;
            $sale->profit = $profit;

            $currentPaid = $sale->customerPayments()->sum('amount');

            if ($hasNewPayment) {
                $newPayment = $request->input('new_payment');
                $remainingBefore = $finalTotal - $currentPaid;

                if ((int)round($newPayment * 100) > (int)round($remainingBefore * 100)) {
                    DB::rollBack();
                    return back()->withErrors(['new_payment' => 'الدفعة الأخيرة لا يمكن أن تتجاوز المتبقي.'])->withInput();
                }

                $currentPaid += $newPayment;

                CustomerPayment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $newPayment,
                    'payment_date' => now(),
                ]);
            }

            $sale->paid = $currentPaid;
            $sale->remaining = $finalTotal - $currentPaid;
            $sale->save();

            DB::commit();
            return redirect()->route('admin.sales.show', $sale->id)->with('success', 'تم تعديل الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ: ' . $e->getMessage())->withInput();
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
        $ids = $request->input('sales_ids', []);

        if (!empty($ids)) {
            try {
                foreach ($ids as $id) {
                    $sale = Sale::with('saleItems')->find($id);
                    if ($sale) {
                        foreach ($sale->saleItems as $item) {
                            $branch = Branch::find($sale->branch_id);
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
            } catch (\Exception $e) {
                return back()->withErrors('فشل حذف الفواتير: ' . $e->getMessage());
            }
        }

        return back()->withErrors('لم يتم تحديد أي فاتورة.');
    }
}
