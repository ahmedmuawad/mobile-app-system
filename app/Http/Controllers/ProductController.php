<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->get();
        $brands = Brand::all();
        $categories = Category::all();

        return view('admin.views.products.index', compact('products', 'brands', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $branches = Branch::all();
        return view('admin.views.products.create', compact('categories', 'brands', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'purchase_price'    => 'required|numeric|min:0',
            'sale_price'        => 'required|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'is_tax_included'   => 'required|boolean',
            'tax_percentage'    => 'nullable|numeric|min:0|max:100',
            'barcode'           => 'nullable|string|max:20|unique:products,barcode',
            'image'             => 'nullable|image|max:2048',
            'branch_price'      => 'array',
            'branch_stock'      => 'array',
            'branch_price.*'    => 'nullable|numeric|min:0',
            'branch_stock.*'    => 'nullable|integer|min:0',
        ]);

        // توليد باركود تلقائي إن لم يتم إدخاله
        if (empty($validated['barcode'])) {
            $validated['barcode'] = strtoupper(substr(uniqid(dechex(rand(100, 999))), -9));
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        // ربط الفروع بالسعر والمخزون والضريبة
        $syncData = [];
        foreach ($request->branch_price ?? [] as $branchId => $price) {
            $syncData[$branchId] = [
                'price'             => $price ?? 0,
                'purchase_price'    => $request->branch_purchase_price[$branchId] ?? 0,
                'stock'             => $request->branch_stock[$branchId] ?? 0,
                'is_tax_included'   => $request->branch_tax_included[$branchId] ?? 0,
                'tax_percentage'    => $request->branch_tax_percentage[$branchId] ?? null,
            ];
        }

        if (!empty($syncData)) {
            $product->branches()->sync($syncData);
        }

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج بنجاح.');
    }

    public function edit($id)
    {
        $product = Product::with('branches')->findOrFail($id);
        $categories = Category::all();
        $brands = Brand::all();
        $branches = Branch::all();
        return view('admin.views.products.edit', compact('product', 'categories', 'brands', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'purchase_price'    => 'required|numeric|min:0',
            'sale_price'        => 'required|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'is_tax_included'   => 'required|boolean',
            'tax_percentage'    => 'nullable|numeric|min:0|max:100',
            'barcode'           => 'nullable|string|max:20|unique:products,barcode,' . $product->id,
            'image'             => 'nullable|image|max:2048',
            'branch_price'      => 'array',
            'branch_stock'      => 'array',
            'branch_price.*'    => 'nullable|numeric|min:0',
            'branch_stock.*'    => 'nullable|integer|min:0',
        ]);

        // توليد باركود إذا لم يكن موجود
        if (empty($validated['barcode']) && !$product->barcode) {
            $validated['barcode'] = strtoupper(substr(uniqid(dechex(rand(100, 999))), -9));
        }

        // رفع صورة جديدة إن وجدت
        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        // ربط الفروع بالسعر والمخزون والضريبة
        $syncData = [];
        foreach ($request->branch_price ?? [] as $branchId => $price) {
            $syncData[$branchId] = [
                'price'             => $price ?? 0,
                'purchase_price'    => $request->branch_purchase_price[$branchId] ?? 0,
                'stock'             => $request->branch_stock[$branchId] ?? 0,
                'is_tax_included'   => $request->branch_tax_included[$branchId] ?? 0,
                'tax_percentage'    => $request->branch_tax_percentage[$branchId] ?? null,
            ];
        }

        if (!empty($syncData)) {
            $product->branches()->sync($syncData);
        }

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج بنجاح.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'selected_products' => 'required|array',
            'action' => 'required|string|in:delete,generate_barcode',
        ]);

        $products = Product::whereIn('id', $request->selected_products)->get();

        if ($request->action === 'delete') {
            foreach ($products as $product) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->delete();
            }
            return back()->with('success', 'تم حذف المنتجات المحددة بنجاح.');
        }

        if ($request->action === 'generate_barcode') {
            foreach ($products as $product) {
                $product->update([
                    'barcode' => strtoupper(substr(uniqid(dechex(rand(100, 999))), -9))
                ]);
            }
            return back()->with('success', 'تم توليد باركود تلقائي للمنتجات المحددة.');
        }
//       // في حالة عدم تطابق أي من الإجراءات
        return back()->with('error', 'حدث خطأ غير متوقع.');
    }
}
