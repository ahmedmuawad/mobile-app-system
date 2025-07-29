<?php

namespace App\Http\Controllers;
 
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // جلب كل المنتجات
        $products = Product::all();

        // ملاحظة: طالما مجلد العرض هو resources/views/admin/views/products
        // يجب تعديل المسار في view حسب مجلد العرض الفعلي.
        return view('admin.views.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('admin.views.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'purchase_price' => 'required|numeric|min:0',
        'sale_price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'category_id' => 'required|exists:categories,id',
        'image' => 'nullable|image|max:2048',  // لو تضيف صورة
        ]);

        if ($request->hasFile('image')) {
        // حفظ الصورة داخل storage/app/public/products
        $path = $request->file('image')->store('products', 'public');
           
        $validated['image'] = $path; // القيمة تكون: products/filename.jpg
    }

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // (اختياري) لو تحتاج عرض تفاصيل منتج معين
        // $product = Product::findOrFail($id);
        // return view('admin.views.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit($id)
{
    $product = Product::findOrFail($id);
    $categories = \App\Models\Category::all();
    return view('admin.views.products.edit', compact('product', 'categories'));
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price'     => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'category_id'    => 'required|exists:categories,id','image' => 'nullable|image|max:2048',  // لو تضيف صورة
        ]);

        if ($request->hasFile('image')) {
        // حفظ الصورة داخل storage/app/public/products
        $path = $request->file('image')->store('products', 'public');
           
        $validated['image'] = $path; // القيمة تكون: products/filename.jpg
    }

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج بنجاح.');
    }
}
