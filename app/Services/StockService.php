<?php

namespace App\Services;

use App\Models\BranchProduct;
use App\Models\StockMovement;
use App\Models\StockAlert;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Increase stock for a branch/product.
     * NOTE: $branchId first, $productId second.
     */
    public static function increaseStock(int $branchId, int $productId, float $qty, string $movementType = 'purchase', array $meta = [])
    {
        // تأكد إن الفرع موجود
        if (!Branch::where('id', $branchId)->exists()) {
            // بدل ما نكسر التطبيق نرمي Exception واضح
            throw new \InvalidArgumentException("Branch with id {$branchId} does not exist.");
        }

        return DB::transaction(function () use ($branchId, $productId, $qty, $movementType, $meta) {
            $bp = BranchProduct::firstOrCreate(
                ['branch_id' => $branchId, 'product_id' => $productId],
                ['stock' => 0]
            );

            $before = (float) $bp->stock;
            $bp->stock = $before + (float) $qty;
            $bp->save();

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'movement_type' => $movementType,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id' => $meta['reference_id'] ?? null,
                'qty_before' => $before,
                'qty_change' => (float) $qty,
                'qty_after' => $bp->stock,
                'user_id' => $meta['user_id'] ?? null,
                'note' => $meta['note'] ?? null,
            ]);

            self::checkAlert($branchId, $productId, $bp->stock);

            return $movement;
        });
    }

    /**
     * Decrease stock for a branch/product.
     */
    public static function decreaseStock(int $branchId, int $productId, float $qty, string $movementType = 'sale', array $meta = [])
    {
        if (!Branch::where('id', $branchId)->exists()) {
            throw new \InvalidArgumentException("Branch with id {$branchId} does not exist.");
        }

        return DB::transaction(function () use ($branchId, $productId, $qty, $movementType, $meta) {
            $bp = BranchProduct::firstOrCreate(
                ['branch_id' => $branchId, 'product_id' => $productId],
                ['stock' => 0]
            );

            $before = (float) $bp->stock;
            $after = $before - (float) $qty;
            $bp->stock = $after;
            $bp->save();

            $movement = StockMovement::create([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'movement_type' => $movementType,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id' => $meta['reference_id'] ?? null,
                'qty_before' => $before,
                'qty_change' => -abs($qty),
                'qty_after' => $after,
                'user_id' => $meta['user_id'] ?? null,
                'note' => $meta['note'] ?? null,
            ]);

            self::checkAlert($branchId, $productId, $bp->stock);

            return $movement;
        });
    }

    /**
     * Update stock alert (last_notified_at) if threshold reached.
     */
    protected static function checkAlert(int $branchId, int $productId, float $currentStock)
    {
        $alert = StockAlert::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('is_active', 1)
            ->first();

        if ($alert && $currentStock <= (float) $alert->threshold) {
            $alert->last_notified_at = now();
            $alert->save();
            // TODO: dispatch notifications (email/whatsapp/in-app) if you want
        }
    }
}
