<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Repair;
use App\Models\Expense;
use App\Models\Purchase;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekAgo = Carbon::now()->subDays(6)->startOfDay();
        $monthStart = Carbon::now()->startOfMonth();

        // إحصائيات اليوم
        $today_sales     = Sale::whereDate('created_at', $today)->sum('total');
        $today_repairs   = Repair::whereDate('created_at', $today)->sum('total');
        $today_expenses  = Expense::whereDate('date', $today)->sum('amount');
        $today_purchases = Purchase::whereDate('created_at', $today)->sum('total_amount');
        $today_profit    = $today_sales + $today_repairs - $today_expenses;

        // آخر 7 أيام (للرسم البياني)
        $last_7_days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->format('Y-m-d');
            $daily_total = Sale::whereDate('created_at', $day)->sum('total') +
                           Repair::whereDate('created_at', $day)->sum('total');

            $last_7_days->push([
                'date' => $day,
                'total' => $daily_total
            ]);
        }

        // إحصائيات الشهر
        $month_sales     = Sale::whereBetween('created_at', [$monthStart, now()])->sum('total');
        $month_repairs   = Repair::whereBetween('created_at', [$monthStart, now()])->sum('total');
        $month_expenses  = Expense::whereBetween('date', [$monthStart, now()])->sum('amount');
        $month_purchases = Purchase::whereBetween('created_at', [$monthStart, now()])->sum('total_amount');
        $month_profit    = $month_sales + $month_repairs - $month_expenses;

        return view('home', compact(
            'today_sales', 'today_expenses', 'today_repairs', 'today_purchases', 'today_profit',
            'month_sales', 'month_expenses', 'month_repairs', 'month_purchases', 'month_profit',
            'last_7_days'
        ));
    }
}
