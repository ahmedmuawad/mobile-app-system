<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * عرض قائمة المبيعات
     */
    public function index()
    {
        // نجيب كل المبيعات مع بيانات العميل (لو موجود)
        $sales = Sale::with('customer')->latest()->paginate(15);

        return view('admin.views.sales.index', compact('sales'));
    }

    /**
     * صفحة إنشاء فاتورة مبيعات جديدة
     */
    public function create()
    {
        // نجيب كل المنتجات والزبائن لاستخدامهم في الفاتورة
        $products = Product::all();
        $customers = Customer::all();

        return view('admin.views.sales.create', compact('products', 'customers'));
    }

    /**
     * تخزين فاتورة المبيعات الجديدة مع تفاصيل الأصناف
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
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
            $sale->save();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                $quantity = $item['quantity'];
                $sale_price = $item['sale_price'];
                $purchase_price = $product->purchase_price;

                $subtotal = $sale_price * $quantity;
                $profit = ($saleItem->sale_price - $saleItem->cost_at_sale) * $saleItem->quantity;

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

            $sale->total = $finalTotal;
            $sale->discount = $discount;
            $sale->profit = $total_profit;
            $sale->save();

            DB::commit();

        return redirect()->route('admin.sales.show', $sale->id)->with('success', 'تم إنشاء الفاتورة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * عرض تفاصيل فاتورة معينة
     */
    public function show($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);
        return view('admin.views.sales.show', compact('sale'));
    }

    /**
     * صفحة تعديل الفاتورة (اختياري، ممكن نضيفها لاحقاً)
     */
    public function edit($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);
        $products = Product::all();
        $customers = Customer::all();

        return view('admin.views.sales.edit', compact('sale', 'products', 'customers'));
    }

    /**
     * تحديث الفاتورة (اختياري)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sale_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($id);
            $sale->customer_id = $request->customer_id;
            $sale->customer_name = $request->customer_name;

            $total = 0;
            $profit = 0;

            // حذف الأصناف القديمة
            $sale->saleItems()->delete();

            // إضافة الأصناف الجديدة
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
                    'purchase_price' => $purchase_price, // ممكن تستغنى عنه
                    'cost_at_sale' => $purchase_price,   // ✅ السعر المثبّت وقت البيع

                ]);
            }

            $discount = $request->input('discount', 0);
            $finalTotal = $total - $discount;

            $sale->total = $finalTotal;
            $sale->discount = $discount;
            $sale->profit = $profit;
            $sale->save();

            DB::commit();

            return redirect()->route('admin.sales.show', $sale->id)->with('success', 'تم تعديل الفاتورة بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }



    /**
     * حذف فاتورة مبيعات (اختياري)
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();

        return redirect()->route('admin.sales.index')->with('success', 'تم حذف الفاتورة بنجاح.');
    }
}
