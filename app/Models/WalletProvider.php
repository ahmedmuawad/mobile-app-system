<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'daily_send_limit',
        'daily_receive_limit',
        'daily_bill_limit',
        'monthly_limit',
    ];

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    // إجمالي المبلغ المُستخدم اليوم حسب نوع العملية
    public function usedDailyAmountByType(string $type): float
    {
        return $this->wallets()
            ->join('wallet_transactions', 'wallets.id', '=', 'wallet_transactions.wallet_id')
            ->where('wallet_transactions.type', $type)
            ->whereDate('wallet_transactions.created_at', now()->toDateString())
            ->sum('wallet_transactions.amount');
    }

    // إجمالي المبلغ المُستخدم خلال الشهر (لكل العمليات)
    public function usedMonthlyAmount(): float
    {
        return $this->wallets()
            ->join('wallet_transactions', 'wallets.id', '=', 'wallet_transactions.wallet_id')
            ->whereMonth('wallet_transactions.created_at', now()->month)
            ->whereYear('wallet_transactions.created_at', now()->year)
            ->sum('wallet_transactions.amount');
    }

    // الحد المتبقي اليوم حسب نوع العملية
    public function getRemainingDailyByType(string $type): float
    {
        return max(0, $this->getDailyLimitByType($type) - $this->usedDailyAmountByType($type));
    }

    // 🔴 هذه الدالة كانت ناقصة – أضفناها هنا:
    public function getDailyLimitByType(string $type): float
    {
        return match ($type) {
            'send' => $this->daily_send_limit ?? 0,
            'receive' => $this->daily_receive_limit ?? 0,
            'bill' => $this->daily_bill_limit ?? 0,
            default => 0,
        };
    }

    // الحد المتبقي الشهري
    public function getRemainingMonthlyAttribute(): float
    {
        return max(0, $this->monthly_limit - $this->usedMonthlyAmount());
    }
}
