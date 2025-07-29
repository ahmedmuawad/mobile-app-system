<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
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
            $products = Product::all();
            $customers = Customer::all();
            return view('admin.views.sales.create', compact('products', 'customers'));
        }

    public function store(Request $request)
        {
            $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'nullable|string|max:255',
                'discount' => 'nullable|numeric|min:0',
                'initial_payment' => 'nullable|numeric|min:0', // ✅
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
                $sale->total = 0;
                $sale->profit = 0;
                $sale->discount = 0;
                $sale->paid = 0;
                $sale->remaining = 0;
                $sale->save();

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    $quantity = $item['quantity'];
                    $sale_price = $item['sale_price'];
                    $purchase_price = $product->purchase_price;

                    $subtotal = $sale_price * $quantity;
                    $profit = ($sale_price - $purchase_price) * $quantity;

                    $total += $subtotal;
                    $total_profit += $profit;

                    $saleItem = new SaleItem();
                    $saleItem->sale_id = $sale->id;
                    $saleItem->product_id = $product->id;
                    $saleItem->product_name = $product->name;
                    $saleItem->quantity = $quantity;
                    $saleItem->sale_price = $sale_price;
                    $saleItem->purchase_price = $purchase_price;
                    $saleItem->save();
                }

                $discount = $request->input('discount', 0);
                $finalTotal = $total - $discount;
                $initialPayment = $request->input('initial_payment', 0);

                $sale->total = $finalTotal;
                $sale->discount = $discount;
                $sale->profit = $total_profit;

                // ✅ دفعة أولية (لو موجودة)
                if ($initialPayment > 0 && $sale->customer_id) {
                    $sale->paid = $initialPayment;
                    $sale->remaining = $finalTotal - $initialPayment;

                    $sale->customerPayments()->create([
                        'amount' => $initialPayment,
                        'payment_date' => now(),
                    ]);
                } else {
                    $sale->paid = 0;
                    $sale->remaining = $finalTotal;
                }

                $sale->save();

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
            return view('admin.views.sales.show', compact('sale'));
        }

    public function edit($id)
        {
            $sale = Sale::with('saleItems')->findOrFail($id);
            $products = Product::all();
            $customers = Customer::all();

            return view('admin.views.sales.edit', compact('sale', 'products', 'customers'))
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
                $sale = Sale::with('customerPayments')->findOrFail($id);

                $finalTotal = $sale->total;
                $discount = $sale->discount;
                $profit = $sale->profit;

                if ($hasItems) {
                    $sale->customer_id = $request->customer_id;
                    $sale->customer_name = $request->customer_name;

                    $total = 0;
                    $profit = 0;

                    $sale->saleItems()->delete();

                    foreach ($request->items as $item) {
                        $product = Product::findOrFail($item['product_id']);
                        $quantity = $item['quantity'];
                        $sale_price = $item['sale_price'];
                        $purchase_price = $product->purchase_price;

                        $subtotal = $sale_price * $quantity;
                        $profit += ($sale_price - $purchase_price) * $quantity;
                        $total += $subtotal;

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $quantity,
                            'sale_price' => $sale_price,
                            'purchase_price' => $purchase_price,
                            'cost_at_sale' => $purchase_price,
                        ]);
                    }

                    $discount = $request->input('discount', 0);
                    $finalTotal = $total - $discount;
                }

                $currentPaid = $sale->customerPayments()->sum('amount');
                $finalPayment = floatval($request->input('new_payment', 0));
                $expectedRemaining = $finalTotal - $currentPaid;

                \Log::info('🔍 [تعديل فاتورة] Final Total: ' . $finalTotal);
                \Log::info('🔍 [تعديل فاتورة] Current Paid: ' . $currentPaid);
                \Log::info('🔍 [تعديل فاتورة] Final Payment Input: ' . $finalPayment);
                \Log::info('🔍 [تعديل فاتورة] Expected Remaining: ' . $expectedRemaining);

                if ($finalPayment > 0) {
                    $intExpected = intval(round($expectedRemaining * 100));
                    $intPayment = intval(round($finalPayment * 100));

                    \Log::info('🔍 [تعديل فاتورة] intExpected: ' . $intExpected);
                    \Log::info('🔍 [تعديل فاتورة] intPayment: ' . $intPayment);

                    if ($intPayment > $intExpected) {
                        DB::rollBack();
                        \Log::warning('🚫 الدفعة الأخيرة تتجاوز المتبقي.');
                        return back()->withErrors(['new_payment' => 'الدفعة الأخيرة لا يمكن أن تتجاوز المتبقي.'])->withInput();
                    }

                    CustomerPayment::create([
                        'sale_id' => $sale->id,
                        'customer_id' => $sale->customer_id,
                        'amount' => $finalPayment,
                        'payment_date' => now(),
                    ]);

                    $currentPaid += $finalPayment;
                }

                $sale->total = $finalTotal;
                $sale->discount = $discount;
                $sale->profit = $profit;
                $sale->paid = $currentPaid;
                $sale->remaining = $finalTotal - $currentPaid;
                $sale->save();

                DB::commit();

                \Log::info('✅ تم تحديث الفاتورة بنجاح.');

                return redirect()->route('admin.sales.show', $sale->id)->with('success', 'تم تعديل الفاتورة بنجاح.');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('❌ خطأ أثناء تعديل الفاتورة: ' . $e->getMessage());
                return back()->withErrors('حدث خطأ: ' . $e->getMessage())->withInput();
            }
        }



    public function destroy($id)
        {
            $sale = Sale::findOrFail($id);
            $sale->delete();
            return redirect()->route('admin.sales.index')->with('success', 'تم حذف الفاتورة بنجاح.');
        }
}
