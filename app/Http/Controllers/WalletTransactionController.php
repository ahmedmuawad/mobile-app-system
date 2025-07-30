<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
   public function index()
{
    $transactions = WalletTransaction::with(['wallet.provider'])->latest()->get();

    // نستخرج المحافظ الفريدة المرتبطة بالحركات
    $wallets = $transactions->pluck('wallet')->unique('id')->values();

    return view('admin.views.wallet_transactions.index', compact('transactions', 'wallets'));
}


    public function create()
{
    $wallets = Wallet::with('provider')->get();

    $limits = [];
    foreach ($wallets as $wallet) {
        $provider = $wallet->provider;
        $limits[$wallet->id] = [
            'provider' => $provider->name,
            'remaining_daily_send' => $provider->getRemainingDailyByType('send'),
            'remaining_daily_receive' => $provider->getRemainingDailyByType('receive'),
            'remaining_daily_bill' => $provider->getRemainingDailyByType('bill'),
            'remaining_monthly' => $provider->remaining_monthly,
        ];
    }

    return view('admin.views.wallet_transactions.create', compact('wallets', 'limits'));
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'type' => 'required|in:send,receive,bill',
            'amount' => 'required|numeric|min:0.01',
            'commission' => 'nullable|numeric|min:0',
            'target_number' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $wallet = Wallet::with('provider')->findOrFail($data['wallet_id']);
        $provider = $wallet->provider;

        $usedDaily = $provider->usedDailyAmountByType($data['type']);
        $remainingDaily = $provider->getRemainingDailyByType($data['type']);

        $usedMonthly = $provider->usedMonthlyAmount();
        $newMonthlyTotal = $usedMonthly + $data['amount'];

        if ($data['amount'] > $remainingDaily) {
            return redirect()->back()->withInput()->withErrors([
                'amount' => 'تم تجاوز الحد اليومي لعملية ' . $data['type'] . '. المتبقي: ' . number_format($remainingDaily, 2),
            ]);
        }

        if ($newMonthlyTotal > $provider->monthly_limit) {
            return redirect()->back()->withInput()->withErrors([
                'amount' => 'تم تجاوز الحد الشهري لهذا المزود. المتبقي: ' . number_format($provider->monthly_limit - $usedMonthly, 2),
            ]);
        }

        WalletTransaction::create($data);
        return redirect()->route('admin.wallet_transactions.index')->with('success', 'تم إنشاء العملية بنجاح.');
    }

    public function show(WalletTransaction $walletTransaction)
    {
        return view('admin.transactions.show', compact('walletTransaction'));
    }

    public function edit(WalletTransaction $walletTransaction)
    {
        $wallets = Wallet::with('provider')->get();
        return view('admin.transactions.edit', compact('walletTransaction', 'wallets'));
    }

    public function update(Request $request, WalletTransaction $walletTransaction)
    {
        $data = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'type' => 'required|in:send,receive,bill',
            'amount' => 'required|numeric|min:0.01',
            'commission' => 'nullable|numeric|min:0',
            'target_number' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $wallet = Wallet::with('provider')->findOrFail($data['wallet_id']);
        $provider = $wallet->provider;

        // خصم العملية القديمة من المبلغ المُستخدم
        $oldDailyUsed = $provider->usedDailyAmountByType($walletTransaction->type) - $walletTransaction->amount;
        $oldMonthlyUsed = $provider->usedMonthlyAmount() - $walletTransaction->amount;

        $newDailyTotal = $oldDailyUsed + $data['amount'];
        $newMonthlyTotal = $oldMonthlyUsed + $data['amount'];

        $dailyLimitColumn = match ($data['type']) {
            'send' => $provider->daily_send_limit,
            'receive' => $provider->daily_receive_limit,
            'bill' => $provider->daily_bill_limit,
            default => 0,
        };

        if ($newDailyTotal > $dailyLimitColumn) {
            return redirect()->back()->withInput()->withErrors([
                'amount' => 'تم تجاوز الحد اليومي لعملية ' . $data['type'] . '. المتبقي: ' . number_format($dailyLimitColumn - $oldDailyUsed, 2),
            ]);
        }

        if ($newMonthlyTotal > $provider->monthly_limit) {
            return redirect()->back()->withInput()->withErrors([
                'amount' => 'تم تجاوز الحد الشهري لهذا المزود. المتبقي: ' . number_format($provider->monthly_limit - $oldMonthlyUsed, 2),
            ]);
        }

        $walletTransaction->update($data);
        return redirect()->route('admin.wallet_transactions.index')->with('success', 'تم تعديل العملية بنجاح.');
    }

    public function destroy(WalletTransaction $walletTransaction)
    {
        $walletTransaction->delete();
        return redirect()->route('admin.wallet_transactions.index')->with('success', 'تم حذف العملية.');
    }
}
