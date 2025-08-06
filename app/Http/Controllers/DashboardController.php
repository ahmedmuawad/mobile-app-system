<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\SparePart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
public function index()
{
    $branch_id = session('current_branch_id');
    $today = today();

    // مبيعات اليوم (إجمالي الفاتورة)
    $today_sales_total = $this->applyBranchFilter(Sale::query(), $branch_id)
        ->whereDate('created_at', $today)
        ->sum('total'); // غيّر 'total' حسب اسم العمود الصحيح

    // مصروفات اليوم
    $today_expenses = $this->applyBranchFilter(Expense::query(), $branch_id)
        ->whereDate('created_at', $today)
        ->sum('amount');

    // أرباح مبيعات اليوم (منتجات)
    $today_sales_product_profit = $this->calculateSalesProductProfit($branch_id, $today);

    // أرباح قطع غيار الصيانة اليوم
    $today_repair_product_profit = $this->calculateRepairPartsProfit($today, $branch_id);

    // أرباح مصنعية الصيانة اليوم
    $today_repair_labor_profit = $this->applyBranchFilter(Repair::query(), $branch_id)
        ->whereDate('created_at', $today)
        ->sum('repair_cost');

    // مشتريات اليوم (إجمالي الفاتورة)
    $today_purchases_total = $this->applyBranchFilter(Purchase::query(), $branch_id)
        ->whereDate('created_at', $today)
        ->sum('total_amount'); // غيّر 'total' حسب اسم العمود الصحيح

    // أرباح اليوم الصافية
    $today_profit = $today_sales_product_profit + $today_repair_product_profit + $today_repair_labor_profit - $today_expenses;

    // بداية ونهاية الشهر الحالي
    $startOfMonth = $today->copy()->startOfMonth();
    $endOfMonth = $today->copy()->endOfMonth();

    // مصروفات الشهر
    $month_expenses = $this->applyBranchFilter(Expense::query(), $branch_id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('amount');

    // مبيعات الشهر (إجمالي الفاتورة)
    $month_sales_total = $this->applyBranchFilter(Sale::query(), $branch_id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('total');

    // أرباح مبيعات الشهر (منتجات)
    $month_sales_product_profit = $this->calculateSalesProductProfit($branch_id, $startOfMonth, $endOfMonth);

    // أرباح قطع غيار الصيانة للشهر
    $month_repair_product_profit = $this->calculateRepairPartsProfit($startOfMonth, $branch_id, $endOfMonth);

    // أرباح مصنعية الصيانة للشهر
    $month_repair_labor_profit = $this->applyBranchFilter(Repair::query(), $branch_id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('repair_cost');
    // إصلاحات اليوم (إجمالي الفاتورة)
    $today_repairs = $this->applyBranchFilter(Repair::query(), $branch_id)
    ->whereDate('created_at', $today)
    ->sum('total');  // أو العمود المناسب مثل 'repair_cost' أو 'final_price'
    // إصلاحات الشهر (إجمالي الفاتورة)
    $month_repairs = $this->applyBranchFilter(Repair::query(), $branch_id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('total'); // نفس ملاحظة العمود السابق

    // مشتريات الشهر (إجمالي الفاتورة)
    $month_purchases_total = $this->applyBranchFilter(Purchase::query(), $branch_id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('total_amount');

    // أرباح الشهر الصافية
    $month_profit = $month_sales_product_profit + $month_repair_product_profit + $month_repair_labor_profit - $month_expenses;

    // رسم بياني لآخر 7 أيام
    $last_7_days = collect();

    foreach (range(6, 0) as $i) {
        $date = $today->copy()->subDays($i);

        $daily_sales = $this->calculateSalesProductProfit($branch_id, $date);
        $daily_repairs_parts = $this->calculateRepairPartsProfit($date, $branch_id);
        $daily_repairs_labor = $this->applyBranchFilter(Repair::query(), $branch_id)
            ->whereDate('created_at', $date)
            ->sum('repair_cost');

        $total = $daily_sales + $daily_repairs_parts + $daily_repairs_labor;

        $last_7_days->push([
            'date' => $date->format('Y-m-d'),
            'total' => $total,
        ]);
    }
// ✅ حساب كروت الدرج الجديدة
$sales_drawer = $today_sales_total - $today_expenses - $today_purchases_total;
$repair_drawer = $today_repairs;
$total_drawer = $sales_drawer + $repair_drawer;

return view('home', [
    'today_expenses' => $today_expenses,
    'today_sales_product_profit' => $today_sales_product_profit,
    'today_repair_product_profit' => $today_repair_product_profit,
    'today_repair_labor_profit' => $today_repair_labor_profit,
    'today_profit' => $today_profit,
    'month_expenses' => $month_expenses,
    'month_sales_product_profit' => $month_sales_product_profit,
    'month_repair_product_profit' => $month_repair_product_profit,
    'month_repair_labor_profit' => $month_repair_labor_profit,
    'month_profit' => $month_profit,
    'last_7_days' => $last_7_days,
    'today_sales_total' => $today_sales_total,
    'today_purchases_total' => $today_purchases_total,
    'month_sales_total' => $month_sales_total,
    'month_purchases_total' => $month_purchases_total,
    'today_repairs' => $today_repairs,
    'month_repairs' => $month_repairs,
        'sales_drawer' => $sales_drawer,
    'repair_drawer' => $repair_drawer,
    'total_drawer' => $total_drawer,


    // ✅ أضف هذا هنا صراحة
    'today_sales_profit' => $today_sales_product_profit + $today_repair_product_profit,
    'today_sales' => $today_sales_total,
    'today_purchases' => $today_purchases_total,
    'month_sales' => $month_sales_total,
    'month_purchases' => $month_purchases_total,
]);

}

protected function calculateSalesProductProfit($branchId, $startDate, $endDate = null)
{
    $sales = Sale::with('saleItems')
        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
        ->when($endDate,
            fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]),
            fn($q) => $q->whereDate('created_at', $startDate)
        )
        ->get();

    $profit = 0;

    foreach ($sales as $sale) {
        foreach ($sale->saleItems as $item) {
            $price = $item->sale_price; // سعر البيع
            $purchase_price = $item->purchase_price ?? 0; // سعر الشراء وقت البيع
            $quantity = $item->quantity;

            $profit += ($price - $purchase_price) * $quantity;
        }
    }

    return $profit;
}

    /**
     * حساب أرباح قطع غيار الصيانة
     */
    protected function calculateRepairPartsProfit($startDate, $branchId, $endDate = null)
    {
        $repairs = Repair::with('spareParts')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]), fn($q) => $q->whereDate('created_at', $startDate))
            ->get();

        $profit = 0;

        foreach ($repairs as $repair) {
            foreach ($repair->spareParts as $sparePart) {
                $branchProduct = DB::table('branch_product')
                    ->where('branch_id', $repair->branch_id)
                    ->where('product_id', $sparePart->id)
                    ->first();

                if (!$branchProduct) continue;

                $price = $branchProduct->price;
                if (!$branchProduct->is_tax_included && $branchProduct->tax_percentage !== null) {
                    $price *= 1 + ($branchProduct->tax_percentage / 100);
                }

                $purchase_price = $branchProduct->purchase_price;
                $quantity = $sparePart->pivot->quantity;

                $profit += ($price - $purchase_price) * $quantity;
            }
        }

        return $profit;
    }

    /**
     * فلترة البيانات حسب الفرع
     */
    protected function applyBranchFilter($query, $branchId)
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }
}
