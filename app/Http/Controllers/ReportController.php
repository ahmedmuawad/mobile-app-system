<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;

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
}
