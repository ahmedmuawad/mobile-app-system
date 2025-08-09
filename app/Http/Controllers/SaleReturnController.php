<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.price' => 'required|numeric',
        ]);

        $userId = Auth::id();

        DB::beginTransaction();
        try {
            $sale = Sale::findOrFail($data['sale_id']);

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'branch_id' => $data['branch_id'],
                'customer_id' => $sale->customer_id ?? null,
                'user_id' => $userId,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $it) {
                $lineTotal = $it['price'] * $it['quantity'];
                $total += $lineTotal;

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'product_id' => $it['product_id'],
                    'branch_id' => $data['branch_id'],
                    'quantity' => $it['quantity'],
                    'price' => $it['price'],
                ]);

                StockService::increaseStock(
                    $data['branch_id'],
                    $it['product_id'],
                    $it['quantity'],
                    'sale_return',
                    [
                        'reference_type' => SaleReturn::class,
                        'reference_id' => $saleReturn->id,
                        'user_id' => $userId,
                        'note' => 'Sale Return #' . $saleReturn->id,
                    ]
                );
            }

            $saleReturn->total = $total;
            $saleReturn->save();

            DB::commit();
            return response()->json(['success' => true, 'sale_return_id' => $saleReturn->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
