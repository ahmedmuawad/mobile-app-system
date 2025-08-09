<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('id', 'desc')->get();
        return view('admin.views.payment_methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('admin.views.payment_methods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        PaymentMethod::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.payment-methods.index')->with('success', 'تم إضافة طريقة الدفع بنجاح');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.views.payment_methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $paymentMethod->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.payment-methods.index')->with('success', 'تم تحديث طريقة الدفع بنجاح');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->route('admin.payment-methods.index')->with('success', 'تم حذف طريقة الدفع');
    }
}
