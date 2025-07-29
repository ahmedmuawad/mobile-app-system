<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest()->paginate(20);
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
        ]);

        Expense::create($request->all());

        return redirect()->route('admin.expenses.index')->with('success', '✅ تم إضافة المصروف بنجاح.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.views.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric|min:0',
            'date'        => 'required|date',
        ]);

        $expense->update($request->all());

        return redirect()->route('admin.expenses.index')->with('success', '✅ تم تحديث المصروف بنجاح.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', '🗑️ تم حذف المصروف بنجاح.');
    }
}
