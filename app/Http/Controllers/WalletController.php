<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletProvider;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::with('provider')->get();
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
            'owner_name' => 'nullable|string',
        ]);

        Wallet::create($data);
        return redirect()->route('admin.wallets.index')->with('success', 'Wallet created.');
    }

    public function edit(Wallet $wallet)
    {
        $providers = WalletProvider::all();
        return view('admin.views.wallets.edit', compact('wallet', 'providers'));
    }

    public function update(Request $request, Wallet $wallet)
    {
        $data = $request->validate([
            'number' => 'required|string|unique:wallets,number,' . $wallet->id,
            'wallet_provider_id' => 'required|exists:wallet_providers,id',
            'owner_name' => 'nullable|string',
        ]);

        $wallet->update($data);
        return redirect()->route('admin.wallets.index')->with('success', 'Wallet updated.');
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return redirect()->route('admin.wallets.index')->with('success', 'Wallet deleted.');
    }
}
