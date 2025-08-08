<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageModuleController extends Controller
{
    // عرض الموديولات الخاصة بالباقة
    public function edit($id)
    {
        $package = DB::table('packages')->where('id', $id)->first();

        if (!$package) {
            abort(404, 'Package not found');
        }

        $modules = DB::table('modules')->orderBy('sort_order', 'asc')->get();

        $assignedModuleIds = DB::table('module_package')
            ->where('package_id', $id)
            ->pluck('module_id')
            ->toArray();

        return view('admin.views.packages.modules', compact('package', 'modules', 'assignedModuleIds'));
    }

    // تحديث الموديولات الخاصة بالباقة
    public function update(Request $request, $id)
    {
        $package = DB::table('packages')->where('id', $id)->first();

        if (!$package) {
            abort(404, 'Package not found');
        }

        $moduleIds = $request->input('modules', []);

        DB::table('module_package')->where('package_id', $id)->delete();

        foreach ($moduleIds as $mid) {
            DB::table('module_package')->insert([
                'package_id' => $id,
                'module_id' => $mid,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.packages.index')->with('success', 'تم تحديث الموديولات الخاصة بالباقة بنجاح');
    }
}
