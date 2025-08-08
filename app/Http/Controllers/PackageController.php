<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Module;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('modules')->get();
        return view('admin.views.packages.index', compact('packages'));
    }

    public function create()
    {
        $modules = Module::all();
        return view('admin.views.packages.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "price" => "required|numeric|min:0",
            "modules" => "array",
            "modules.*" => "exists:modules,id"
        ]);

        $package = Package::create([
            "name" => $data["name"],
            "price" => $data["price"],
        ]);

        if (!empty($data["modules"])) {
            $package->modules()->sync($data["modules"]);
        }

        return redirect()->route('admin.packages.index')->with('success', 'تم إنشاء الباقة بنجاح');
    }

    public function edit(Package $package)
    {
        $modules = Module::all();
        return view('admin.views.packages.edit', compact('package', 'modules'));
    }

    public function update(Request $request, Package $package)
    {
        $data = $request->validate([
            "name" => "required|string|max:255",
            "price" => "required|numeric|min:0",
            "modules" => "array",
            "modules.*" => "exists:modules,id"
        ]);

        $package->update([
            "name" => $data["name"],
            "price" => $data["price"],
        ]);

        if (!empty($data["modules"])) {
            $package->modules()->sync($data["modules"]);
        } else {
            $package->modules()->detach();
        }

        return redirect()->route('admin.packages.index')->with('success', 'تم تحديث الباقة بنجاح');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'تم حذف الباقة بنجاح');
    }
}
