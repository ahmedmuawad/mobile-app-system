<?php

namespace App\Http\Controllers;

use App\Models\WalletProvider;
use Illuminate\Http\Request;

class WalletProviderController extends Controller
{
    public function index()
    {
        $providers = WalletProvider::all();
        return view('admin.views.wallet_providers.index', compact('providers'));

    }

    public function create()
    {
        return view('admin.views.wallet_providers.create');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'daily_send_limit' => 'required|numeric|min:0',
        'daily_receive_limit' => 'required|numeric|min:0',
        'daily_bill_limit' => 'required|numeric|min:0',
        'monthly_limit' => 'required|numeric|min:0',
    ]);

    WalletProvider::create($validated);

    return redirect()->route('admin.wallet_providers.index')->with('success', 'تم إضافة المزود بنجاح');
}


    public function edit(WalletProvider $walletProvider)
    {
        return view('admin.views.wallet_providers.edit', compact('walletProvider'));
    }

    public function update(Request $request, WalletProvider $walletProvider)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'daily_limit' => 'required|numeric',
            'monthly_limit' => 'required|numeric',
        ]);

        $walletProvider->update($data);
        return redirect()->route('admin.wallet_providers.index')->with('success', 'Wallet Provider updated.');
    }

    public function destroy(WalletProvider $walletProvider)
    {
        $walletProvider->delete();
        return redirect()->route('admin.wallet_providers.index')->with('success', 'Wallet Provider deleted.');
    }
}
