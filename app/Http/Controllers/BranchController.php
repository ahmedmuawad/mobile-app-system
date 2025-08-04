<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::with('company')->latest()->get();
        return view('admin.views.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.views.branches.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->company_id) {
            return redirect()->back()->with('error', 'لا يمكن إنشاء الفرع بدون شركة مرتبطة بالمستخدم.');
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'nullable|string|max:1000',
            'phone'      => 'nullable|string|max:20',
            'is_main'    => 'boolean',
            'is_active'  => 'boolean',
        ]);

        $validated['is_main'] = $request->boolean('is_main');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['company_id'] = auth()->user()->company_id;

        Branch::create($validated);

        return redirect()->route('admin.branches.index')->with('success', 'تم إضافة الفرع بنجاح');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('admin.views.branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'nullable|string|max:1000',
            'phone'      => 'nullable|string|max:20',
            'is_main'    => 'boolean',
            'is_active'  => 'boolean',
        ]);

        $branch->update([
            'name'       => $request->name,
            'address'    => $request->address,
            'phone'      => $request->phone,
            'is_main'    => $request->is_main ?? false,
            'is_active'  => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'تم تحديث بيانات الفرع بنجاح');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'تم حذف الفرع بنجاح');
    }

    // ✅ تغيير الفرع الحالي أو اختيار "كل الفروع"
    public function changeBranch($id)
    {
        $user = auth()->user();

        // في حالة "كل الفروع"
        if ($id === 'all') {
            session()->forget('current_branch_id');
        }
        // التحقق من صلاحية المستخدم على الفرع المحدد
        elseif ($user->branches()->where('branch_id', $id)->exists()) {
            session(['current_branch_id' => $id]);
        }

        return redirect()->back();
    }

}
