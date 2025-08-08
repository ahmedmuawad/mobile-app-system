<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Company;
use App\Models\Package;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['company', 'package'])->get();
        return view('admin.views.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $companies = Company::all();
        $packages = Package::all();
        return view('admin.views.subscriptions.create', compact('companies', 'packages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:packages,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'required|in:active,inactive',
        ]);

        $subscription = Subscription::create($data);

        // ربط الاشتراك بالموديولز من الباقة
        $company = Company::find($data['company_id']);
        $company->applySubscription($subscription);

        return redirect()->route('admin.subscriptions.index')->with('success', 'تم إضافة الاشتراك بنجاح');
    }

    public function edit(Subscription $subscription)
    {
        $companies = Company::all();
        $packages = Package::all();
        return view('admin.views.subscriptions.edit', compact('subscription', 'companies', 'packages'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:packages,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'required|in:active,inactive',
        ]);

        $subscription->update($data);

        $company = Company::find($data['company_id']);
        $company->applySubscription($subscription);

        return redirect()->route('admin.subscriptions.index')->with('success', 'تم تحديث الاشتراك بنجاح');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')->with('success', 'تم حذف الاشتراك بنجاح');
    }
}
