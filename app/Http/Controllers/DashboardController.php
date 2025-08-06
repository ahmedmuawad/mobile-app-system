<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $branch_id = session('current_branch_id');

        // مبيعات اليوم
        $today_sales = \App\Models\Sale::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })->whereDate('created_at', today())->sum('total');

        // مصروفات اليوم
        $today_expenses = \App\Models\Expense::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })->whereDate('created_at', today())->sum('amount');

        // // مبيعات الصيانة اليوم
        // $today_repairs = \App\Models\Repair::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereDate('created_at', today())->sum('total');

        // // أرباح قطع غيار الصيانة اليوم
        // $today_repair_product_profit = \App\Models\Repair::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereDate('created_at', today())->sum('profit');

        // // مصنعية الصيانة والسوفتوير اليوم
        // $today_repair_labor_profit = \App\Models\Repair::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereDate('created_at', today())->sum('labor_profit');

        // // مشتريات اليوم
        // $today_purchases = \App\Models\Purchase::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereDate('created_at', today())->sum('total');

        // أرباح اليوم
        $today_profit = \App\Models\Sale::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })->whereDate('created_at', today())->sum('profit');

        // آخر 7 أيام (مبيعات)
        $last_7_days = \App\Models\Sale::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })
        ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
        ->selectRaw('DATE(created_at) as date, SUM(total) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // مبيعات الشهر
        $month_sales = \App\Models\Sale::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })->whereMonth('created_at', now()->month)->sum('total');

        // مصروفات الشهر
        $month_expenses = \App\Models\Expense::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })->whereMonth('created_at', now()->month)->sum('amount');

        // // مبيعات الصيانة الشهر
        // $month_repairs = \App\Models\Repair::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereMonth('created_at', now()->month)->sum('total');

        // // أرباح قطع غيار الصيانة الشهر
        // $month_repair_product_profit = \App\Models\RepairItem::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereMonth('created_at', now()->month)->sum('profit');

        // // مصنعية الصيانة والسوفتوير الشهر
        // $month_repair_labor_profit = \App\Models\Repair::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereMonth('created_at', now()->month)->sum('labor_profit');

        // // مشتريات الشهر
        // $month_purchases = \App\Models\Purchase::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
        //     $q->where('branch_id', $branch_id);
        // })->whereMonth('created_at', now()->month)->sum('total');

        // أرباح الشهر
        $month_profit = \App\Models\Sale::when($branch_id && $branch_id !== 'all', function($q) use ($branch_id) {
            $q->where('branch_id', $branch_id);
        })->whereMonth('created_at', now()->month)->sum('profit');

        return view('home', compact(
            // 'today_sales', 'today_expenses', 'today_repairs', 'today_repair_product_profit', 'today_repair_labor_profit', 'today_purchases', 'today_profit',
            // 'last_7_days',
            // 'month_sales', 'month_expenses', 'month_repairs', 'month_repair_product_profit', 'month_repair_labor_profit', 'month_purchases', 'month_profit'
            'today_sales', 'today_expenses',  'today_profit',
            'last_7_days',
            'month_sales', 'month_expenses', 'month_profit'

        ));
    }
}
