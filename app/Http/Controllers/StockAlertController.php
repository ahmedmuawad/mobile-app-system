<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function index(Request $request)
    {
        $currentBranchId = session('current_branch_id');
        $branches = auth()->user()->branches;
        $categories = Category::all();

        $alerts = StockAlert::with(['product.category', 'branch'])
            ->when($currentBranchId, function ($q) use ($currentBranchId) {
                $q->where('branch_id', $currentBranchId);
            })
            ->when($request->branch_id, function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->when($request->category_id, function ($q) use ($request) {
                $q->whereHas('product', function ($qq) use ($request) {
                    $qq->where('category_id', $request->category_id);
                });
            })
            ->when($request->status !== null, function ($q) use ($request) {
                $q->where('is_active', $request->status);
            })
            ->latest()
            ->get();

        return view('admin.views.stock_alerts.index', compact('alerts', 'branches', 'categories'));
    }

    public function toggleStatus($id)
    {
        $alert = StockAlert::findOrFail($id);
        $alert->update(['is_active' => !$alert->is_active]);

        return back()->with('success', 'تم تحديث حالة التنبيه بنجاح.');
    }
}
