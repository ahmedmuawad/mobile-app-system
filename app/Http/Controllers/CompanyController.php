<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('admin.views.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.views.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'billing_email' => 'required|email',
            'timezone' => 'required|string|max:64',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        $company = Company::create([
            'name' => $data['name'],
            'billing_email' => $data['billing_email'],
            'timezone' => $data['timezone'],
            'subdomain' => Str::slug($data['name']),
        ]);

        User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'company_id' => $company->id,
            'role' => 'company_admin',
        ]);

        return redirect()->route('admin.companies.index')->with('success', 'تم إنشاء الشركة والمستخدم الأدمن بنجاح');
    }

    public function show(Company $company)
    {
        return view('admin.views.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.views.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'billing_email' => 'required|email',
            'timezone' => 'required|string|max:64',
            'subdomain' => 'required|string|max:255',
        ]);

        $company->update($data);

        return redirect()->route('admin.companies.index')->with('success', 'تم تحديث بيانات الشركة');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')->with('success', 'تم حذف الشركة');
    }
}
