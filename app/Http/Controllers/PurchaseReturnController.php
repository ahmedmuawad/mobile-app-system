<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Purchase;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.price' => 'required|numeric',
        ]);

        $userId = Auth::id();

        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($data['purchase_id']);

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'branch_id' => $data['branch_id'],
                'supplier_id' => $purchase->supplier_id ?? null,
                'user_id' => $userId,
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $it) {
                $lineTotal = $it['price'] * $it['quantity'];
                $total += $lineTotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $it['product_id'],
                    'branch_id' => $data['branch_id'],
                    'quantity' => $it['quantity'],
                    'price' => $it['price'],
                ]);

                StockService::decreaseStock(
                    $data['branch_id'],
                    $it['product_id'],
                    $it['quantity'],
                    'purchase_return',
                    [
                        'reference_type' => PurchaseReturn::class,
                        'reference_id' => $purchaseReturn->id,
                        'user_id' => $userId,
                        'note' => 'Purchase Return #' . $purchaseReturn->id,
                    ]
                );
            }

            $purchaseReturn->total = $total;
            $purchaseReturn->save();

            DB::commit();
            return response()->json(['success' => true, 'purchase_return_id' => $purchaseReturn->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
