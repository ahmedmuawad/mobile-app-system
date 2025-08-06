<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    public function index()
    {
        $transactionsQuery = WalletTransaction::with(['wallet.provider'])->latest();

        // فلترة حسب الفرع الحالي إن وجد
        if (session()->has('current_branch_id')) {
            $transactionsQuery->whereHas('wallet', function ($query) {
                $query->where('branch_id', session('current_branch_id'));
            });
        }

        $transactions = $transactionsQuery->get();

        // استخراج المحافظ المرتبطة بالمعاملات فقط
        $wallets = $transactions->pluck('wallet')->unique('id')->values();

        return view('admin.views.wallet_transactions.index', compact('transactions', 'wallets'));
    }

    public function create()
    {
        $walletsQuery = Wallet::with('provider');

        if (session()->has('current_branch_id')) {
            $walletsQuery->where('branch_id', session('current_branch_id'));
        }

        $wallets = $walletsQuery->get();

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

    // تأكد إن المحفظة تابعة للفرع الحالي
    if (session()->has('current_branch_id') && $wallet->branch_id != session('current_branch_id')) {
        return redirect()->back()->withInput()->withErrors([
            'wallet_id' => 'المحفظة غير تابعة للفرع الحالي.',
        ]);
    }

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

    // ✅ أضف الفرع قبل الحفظ
    $data['branch_id'] = $wallet->branch_id;

    WalletTransaction::create($data);

    return redirect()->route('admin.wallet_transactions.index')->with('success', 'تم إنشاء العملية بنجاح.');
}


    public function show(WalletTransaction $walletTransaction)
    {
        // تحقق من صلاحية الفرع
        if (session()->has('current_branch_id') &&
            $walletTransaction->wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بعرض هذه العملية.');
        }

        return view('admin.transactions.show', compact('walletTransaction'));
    }

    public function edit(WalletTransaction $walletTransaction)
    {
        if (session()->has('current_branch_id') &&
            $walletTransaction->wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بتعديل هذه العملية.');
        }

        $walletsQuery = Wallet::with('provider');

        if (session()->has('current_branch_id')) {
            $walletsQuery->where('branch_id', session('current_branch_id'));
        }

        $wallets = $walletsQuery->get();

        return view('admin.views.transactions.edit', compact('walletTransaction', 'wallets'));
    }

    public function update(Request $request, WalletTransaction $walletTransaction)
    {
        if (session()->has('current_branch_id') &&
            $walletTransaction->wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بتعديل هذه العملية.');
        }

        $data = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'type' => 'required|in:send,receive,bill',
            'amount' => 'required|numeric|min:0.01',
            'commission' => 'nullable|numeric|min:0',
            'target_number' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $wallet = Wallet::with('provider')->findOrFail($data['wallet_id']);

        // تحقق إن المحفظة تنتمي لنفس الفرع
        if (session()->has('current_branch_id') && $wallet->branch_id != session('current_branch_id')) {
            return redirect()->back()->withInput()->withErrors([
                'wallet_id' => 'المحفظة غير تابعة للفرع الحالي.',
            ]);
        }

        $provider = $wallet->provider;

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
        if (session()->has('current_branch_id') &&
            $walletTransaction->wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بحذف هذه العملية.');
        }

        $walletTransaction->delete();

        return redirect()->route('admin.wallet_transactions.index')->with('success', 'تم حذف العملية.');
    }
}
