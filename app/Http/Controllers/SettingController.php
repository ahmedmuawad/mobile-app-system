<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        return view('admin.views.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'invoice_footer' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048',

        ]);

        $setting = Setting::first();

        if ($request->hasFile('logo')) {
            // حذف القديم لو موجود
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $setting->logo = $request->file('logo')->store('logos', 'public');
        }

        $setting->update($request->except('logo'));

        return back()->with('success', 'تم تحديث الإعدادات بنجاح.');
    }
}
