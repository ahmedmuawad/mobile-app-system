<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentBranchId = session('current_branch_id');

        $query = Product::with(['category', 'brand']);

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($currentBranchId && $user->branches()->where('branch_id', $currentBranchId)->exists()) {
            $query->whereHas('branches', function ($q) use ($currentBranchId) {
                $q->where('branches.id', $currentBranchId);
            })->with(['branches' => function ($q) use ($currentBranchId) {
                $q->where('branches.id', $currentBranchId);
            }]);
        } else {
            $userBranchIds = $user->branches->pluck('id')->toArray();
            $query->whereHas('branches', function ($q) use ($userBranchIds) {
                $q->whereIn('branches.id', $userBranchIds);
            });
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
        $branches = auth()->user()->branches;
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

        if (str_word_count($request->name) > 10) {
            return back()->withErrors(['name' => '🚫 الحد الأقصى لعدد كلمات اسم المنتج هو 10 كلمات.'])->withInput();
        }

        if (empty($validated['barcode'])) {
            $validated['barcode'] = strtoupper(substr(uniqid(dechex(rand(100, 999))), -9));
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

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

    $userBranches = auth()->user()->branches->pluck('id');
    $currentBranchId = session('current_branch_id');

    if ($currentBranchId) {
        // فرع محدد
        $branches = Branch::where('id', $currentBranchId)
                          ->whereIn('id', $userBranches)
                          ->get();
    } else {
        // كل الفروع
        $branches = Branch::whereIn('id', $userBranches)->get();
    }

    return view('admin.views.products.edit', compact('product', 'categories', 'brands', 'branches'));
}



    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'stock' => 'required|numeric',
            'is_tax_included' => 'required|boolean',
            'tax_percentage' => 'nullable|numeric',
            'barcode' => 'nullable|string|max:20|unique:products,barcode,' . $id,
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|max:2048',
        ]);

        // تحديث بيانات المنتج الأساسية
        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'purchase_price' => $request->purchase_price,
            'sale_price' => $request->sale_price,
            'stock' => $request->stock,
            'is_tax_included' => $request->is_tax_included,
            'tax_percentage' => $request->tax_percentage ?? 0,
            'barcode' => $request->barcode,
            'brand_id' => $request->brand_id,
        ]);

        // حفظ صورة جديدة إن وُجدت
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->update(['image' => $imagePath]);
        }

        // تحديث بيانات الفروع
        foreach ($request->branch_purchase_price ?? [] as $branchId => $purchasePrice) {
            // تحقق أن المستخدم له صلاحية على هذا الفرع
            if (auth()->user()->branches->contains('id', $branchId)) {
                $product->branches()->updateExistingPivot($branchId, [
                    'purchase_price' => $purchasePrice,
                    'price' => $request->branch_price[$branchId] ?? 0,
                    'stock' => $request->branch_stock[$branchId] ?? 0,
                    'is_tax_included' => $request->branch_tax_included[$branchId] ?? 0,
                    'tax_percentage' => $request->branch_tax_percentage[$branchId] ?? 0,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', '✅ تم تحديث المنتج بنجاح.');
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

        return back()->with('error', 'حدث خطأ غير متوقع.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'products_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('products_file')->getRealPath();
        $rows = Excel::toArray([], $path)[0];

        unset($rows[0]);

        foreach ($rows as $row) {
            if (!isset($row[0]) || empty($row[0])) continue;

            Product::create([
                'name'            => $row[0],
                'barcode'         => $row[1],
                'category_id'     => $row[2],
                'brand_id'        => $row[3],
                'sale_price'      => $row[4],
                'purchase_price'  => $row[5],
                'stock'           => $row[6],
                'is_tax_included' => $row[7] ?? 0,
                'tax_percentage'  => $row[8] ?? 0,
            ]);
        }

        return back()->with('success', 'تم استيراد المنتجات بنجاح');
    }
}
