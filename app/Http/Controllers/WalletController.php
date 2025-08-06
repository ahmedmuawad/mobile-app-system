<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Expense;
use App\Models\WalletProvider;
use Illuminate\Http\Request;

class WalletController extends Controller
{
public function index()
{
    $walletsQuery = Wallet::with(['provider', 'transactions']); // جلب المعاملات مع المحافظ

    if (session()->has('current_branch_id')) {
        $walletsQuery->where('branch_id', session('current_branch_id'));
    }

    $wallets = $walletsQuery->get();

    // حساب الرصيد لكل محفظة
    foreach ($wallets as $wallet) {
        $wallet->balance = $wallet->transactions->reduce(function ($carry, $tx) {
            if ($tx->type === 'receive') {
                return $carry + $tx->amount;
            } elseif (in_array($tx->type, ['send', 'bill'])) {
                return $carry - $tx->amount;
            }
            return $carry;
        }, 0);
    }

    return view('admin.views.wallets.index', compact('wallets'));
}


    public function create()
    {
        $providers = WalletProvider::all();
        return view('admin.views.wallets.create', compact('providers'));
    }

public function store(Request $request)
{
    $data = $request->validate([
        'number' => 'required|string|unique:wallets,number',
        'wallet_provider_id' => 'required|exists:wallet_providers,id',
        'owner_name' => 'nullable|string|max:255',
    ]);

    $branchId = session('current_branch_id');

    if (!$branchId) {
        abort(403, 'لم يتم تحديد الفرع الحالي.');
    }

    $data['branch_id'] = $branchId;

    Wallet::create($data);

    return redirect()->route('admin.wallets.index')->with('success', 'تم إنشاء المحفظة بنجاح.');
}

    public function edit(Wallet $wallet)
    {
        // تحقق من أن المحفظة تنتمي للفرع الحالي
        if (session()->has('current_branch_id') && $wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بتعديل هذه المحفظة.');
        }

        $providers = WalletProvider::all();
        return view('admin.views.wallets.edit', compact('wallet', 'providers'));
    }

    public function update(Request $request, Wallet $wallet)
    {
        // تحقق من أن المحفظة تنتمي للفرع الحالي
        if (session()->has('current_branch_id') && $wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بتعديل هذه المحفظة.');
        }

        $data = $request->validate([
            'number' => 'required|string|unique:wallets,number,' . $wallet->id,
            'wallet_provider_id' => 'required|exists:wallet_providers,id',
            'owner_name' => 'nullable|string',
        ]);

        $wallet->update($data);

        return redirect()->route('admin.wallets.index')->with('success', 'تم تعديل المحفظة بنجاح.');
    }

    public function destroy(Wallet $wallet)
    {
        // تحقق من أن المحفظة تنتمي للفرع الحالي
        if (session()->has('current_branch_id') && $wallet->branch_id != session('current_branch_id')) {
            abort(403, 'غير مصرح لك بحذف هذه المحفظة.');
        }

        $wallet->delete();

        return redirect()->route('admin.wallets.index')->with('success', 'تم حذف المحفظة.');
    }


public function storeDeposit(Request $request)
{
    $request->validate([
        'wallet_id' => 'required|exists:wallets,id',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'nullable|string',
        'expense_date' => 'required|date',
    ]);

    $wallet = Wallet::with('provider')->findOrFail($request->wallet_id);

    // تحديد الوصف: من المستخدم أو نص افتراضي
    $description = $request->description ?: 'إيداع من الخزينة';

    // حفظ معاملة الإيداع في wallet_transactions
    $wallet->transactions()->create([
        'branch_id' => $wallet->branch_id,
        'type' => 'depositfromsafe', // إيداع نقدي
        'amount' => $request->amount,
        'description' => $description,
    ]);

    // حفظ كمصروف (نوعه: إيداع محفظة)
    Expense::create([
        'branch_id' => $wallet->branch_id,
        'name' => 'إيداع لمحفظة ' . $wallet->provider->name,
        'description' => $description,
        'amount' => $request->amount,
        'expensable_id' => $wallet->provider->id,
        'expensable_type' => WalletProvider::class,
        'expense_date' => $request->expense_date,
        'wallet_provider_id' => $wallet->provider->id,
    ]);

    return redirect()->route('admin.wallets.index')->with('success', 'تم إيداع الرصيد بنجاح.');
}


}
