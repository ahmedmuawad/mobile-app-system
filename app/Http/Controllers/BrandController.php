<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->get();
        return view('admin.views.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.views.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        Brand::create($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.brands.index')->with('success', 'تم إضافة الماركة بنجاح');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.views.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        $brand->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.brands.index')->with('success', 'تم تحديث بيانات الماركة');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'تم حذف الماركة');
    }
}
