<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
public function index()
{
    $branchId = session('current_branch_id');
    $userBranchIds = auth()->user()->branches->pluck('id')->toArray();

    $expenses = Expense::when($branchId, function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId);
                    }, function ($query) use ($userBranchIds) {
                        return $query->whereIn('branch_id', $userBranchIds);
                    })
                    ->latest()
                    ->paginate(20);

    return view('admin.views.expenses.index', compact('expenses'));
}

    public function create()
    {
        return view('admin.views.expenses.create');
    }

public function store(Request $request)
{
    $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'amount'      => 'required|numeric|min:0',
        'date'        => 'required|date',
        'branch_id'   => 'nullable|exists:branches,id', // فقط لو المستخدم مختار "كل الفروع"
    ]);

    $branchId = session('current_branch_id') ?? $request->branch_id;

    if (!$branchId) {
        return back()->withErrors(['branch_id' => 'الرجاء اختيار الفرع.'])->withInput();
    }

    Expense::create([
        'name'         => $request->name,
        'description'  => $request->description,
        'amount'       => $request->amount,
        'expense_date' => $request->date,
        'branch_id'    => $branchId,
    ]);

    return redirect()->route('admin.expenses.index')->with('success', '✅ تم إضافة المصروف بنجاح.');
}


    public function edit(Expense $expense)
    {
$userBranchIds = auth()->user()->branches->pluck('id')->toArray();
if (!in_array($expense->branch_id, $userBranchIds)) {
    abort(403);
}

        return view('admin.views.expenses.edit', compact('expense'));
    }

public function update(Request $request, Expense $expense)
{
    $userBranchIds = auth()->user()->branches->pluck('id')->toArray();

    if (!in_array($expense->branch_id, $userBranchIds)) {
        abort(403);
    }

    $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'amount'      => 'required|numeric|min:0',
        'date'        => 'required|date',
    ]);

    $expense->update([
        'name'         => $request->name,
        'description'  => $request->description,
        'amount'       => $request->amount,
        'expense_date' => $request->date,
    ]);

    return redirect()->route('admin.expenses.index')->with('success', '✅ تم تحديث المصروف بنجاح.');
}


    public function destroy(Expense $expense)
    {
        if ($expense->branch_id !== session('current_branch_id')) {
            abort(403);
        }

        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', '🗑️ تم حذف المصروف بنجاح.');
    }
}
