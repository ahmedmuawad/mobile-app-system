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

        // حساب أرباح المنتجات في الصيانة (اليوم)
        $today_repair_product_profit = Repair::whereDate('created_at', $today)
            ->get()
            ->sum(function($repair) {
                $product_profit = 0;
                foreach ($repair->spareParts as $sparePart) {
                    // حساب الربح من المنتجات (سعر البيع - سعر الشراء) * الكمية
                    $product_profit += ($sparePart->sale_price - $sparePart->purchase_price) * $sparePart->pivot->quantity;
                }
                return $product_profit;
            });

        // حساب أرباح المصنعية (اليوم)
        $today_repair_labor_profit = Repair::whereDate('created_at', $today)
            ->sum('repair_cost'); // ربح المصنعية فقط

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

        // حساب أرباح المنتجات في الصيانة (الشهر)
        $month_repair_product_profit = Repair::whereBetween('created_at', [$monthStart, now()])
            ->get()
            ->sum(function($repair) {
                $product_profit = 0;
                foreach ($repair->spareParts as $sparePart) {
                    // حساب الربح من المنتجات (سعر البيع - سعر الشراء) * الكمية
                    $product_profit += ($sparePart->sale_price - $sparePart->purchase_price) * $sparePart->pivot->quantity;
                }
                return $product_profit;
            });

        // حساب أرباح المصنعية (الشهر)
        $month_repair_labor_profit = Repair::whereBetween('created_at', [$monthStart, now()])
            ->sum('repair_cost'); // ربح المصنعية فقط

        return view('home', compact(
            'today_sales', 'today_expenses', 'today_repairs', 'today_purchases', 'today_profit',
            'month_sales', 'month_expenses', 'month_repairs', 'month_purchases', 'month_profit',
            'last_7_days',
            'today_repair_product_profit', 'today_repair_labor_profit',  // إضافة أرباح المنتجات والمصنعية لليوم
            'month_repair_product_profit', 'month_repair_labor_profit'   // إضافة أرباح المنتجات والمصنعية للشهر
        ));
    }
}
