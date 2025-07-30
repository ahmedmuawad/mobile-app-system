<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Repair;

class ReportController extends Controller
{
    public function salesReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $report = null;
        $sales = [];

        if ($from && $to) {
            $from = $from . ' 00:00:00';
            $to = $to . ' 23:59:59';

            $report = Sale::whereBetween('created_at', [$from, $to])
                ->selectRaw('COUNT(*) as invoices_count, SUM(total) as total_sales, SUM(profit) as total_profit')
                ->first();

            $sales = Sale::with('customer')->whereBetween('created_at', [$from, $to])->get();
        }

        return view('admin.views.reports.sales', compact('report', 'from', 'to', 'sales'));
    }

    public function purchasesReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $report = null;
        $purchases = [];

        if ($from && $to) {
            $from = $from . ' 00:00:00';
            $to = $to . ' 23:59:59';

            $report = Purchase::whereBetween('created_at', [$from, $to])
                ->selectRaw('
                    COUNT(*) as invoices_count,
                    SUM(total_amount) as total_purchases,
                    SUM(paid_amount) as total_paid
                ')
                ->first();

            $purchases = Purchase::with('supplier')
                ->whereBetween('created_at', [$from, $to])
                ->get();
        }

        return view('admin.views.reports.purchases', compact('report', 'from', 'to', 'purchases'));
    }

public function repairsReport(Request $request)
{
    $from = $request->input('from');
    $to = $request->input('to');

    $report = null;
    $repairs = [];

    if ($from && $to) {
        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        $repairs = Repair::with(['customer', 'spareParts'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get()
            ->map(function ($repair) {
                $parts_sale = 0;
                $purchase_cost = 0;

                foreach ($repair->spareParts as $part) {
                    $qty = $part->pivot->quantity;
                    $parts_sale += $part->sale_price * $qty;
                    $purchase_cost += $part->purchase_price * $qty;
                }

                $repair->parts_sale = $parts_sale;
                $repair->product_profit = $parts_sale - $purchase_cost;
                $repair->labor_profit = $repair->total - $parts_sale;

                return $repair;
            });

        $report = (object)[
            'invoices_count'     => $repairs->count(),
            'total_repairs'      => $repairs->sum('total'),
            'spare_parts_sales'  => $repairs->sum('parts_sale'),
            'product_profit'     => $repairs->sum('product_profit'),
            'labor_profit'       => $repairs->sum('labor_profit'),
        ];
    }

    return view('admin.views.reports.repairs', compact('report', 'from', 'to', 'repairs'));
}
}
