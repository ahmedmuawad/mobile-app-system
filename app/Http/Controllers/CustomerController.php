<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerHistoryExport;

class CustomerController extends Controller
{
    // 1. عرض قائمة العملاء مع صفحة ترقيم (pagination)
    public function index()
    {
    $customers = Customer::with(['sales', 'repairs'])->orderBy('name')->paginate(15);
        return view('admin.views.customers.index', compact('customers'));
    }

    // 2. عرض نموذج إنشاء عميل جديد
    public function create()
    {
        return view('admin.views.customers.create');
    }

    // 3. حفظ عميل جديد بعد التحقق من البيانات
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:customers,email',
        ]);

        Customer::create($request->only('name', 'phone', 'email'));

        return redirect()->route('admin.customers.index')->with('success', 'تم إضافة العميل بنجاح');
    }

    // 4. عرض تفاصيل عميل محدد
    public function show(Customer $customer)
    {
        return view('admin.views.customers.show', compact('customer'));
    }

    // 5. عرض نموذج تعديل عميل موجود
    public function edit(Customer $customer)
    {
        return view('admin.views.customers.edit', compact('customer'));
    }

    // 6. تحديث بيانات العميل بعد التحقق من البيانات
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($request->only('name', 'phone', 'email'));

        return redirect()->route('admin.customers.index')->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    // 7. حذف عميل
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'تم حذف العميل بنجاح');
    }

    // عرض سجل العميل
    public function history(Customer $customer)
    {
        $customer->load([
            'sales.products',
            'sales.branch',
            'sales.payments.paymentMethod',
            'repairs.spareParts',
            'repairs.payments.paymentMethod'
        ]);

        return view('admin.views.customers.history', compact('customer'));
    }

    // تصدير سجل العميل كملف Excel
    public function exportHistory($id)
    {
        $customer = Customer::with([
            'sales.products',
            'sales.branch',
            'sales.payments.paymentMethod',
            'repairs.spareParts',
            'repairs.payments.paymentMethod'
        ])->findOrFail($id);

        return Excel::download(new CustomerHistoryExport($customer), 'customer_history.xlsx');
    }
}
