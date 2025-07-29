<?php
// app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('purchases')->get();
        return view('admin.views.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Supplier::create($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', '✅ تم إضافة المورد بنجاح.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.views.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier->update($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', '✅ تم تحديث بيانات المورد.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', '🗑️ تم حذف المورد.');
    }
}
