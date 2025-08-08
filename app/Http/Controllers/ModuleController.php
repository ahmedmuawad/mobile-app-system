<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::all();
        return view('admin.views.modules.index', compact('modules'));
    }

    public function create()
    {
        return view('admin.views.modules.create');
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric',
        'modules' => 'array',
        'modules.*' => 'exists:modules,id',
    ]);

    $package = Package::create([
        'name' => $request->name,
        'price' => $request->price,
    ]);

    if ($request->has('modules')) {
        $package->modules()->sync($request->modules);
    }

    return redirect()->route('admin.packages.index')->with('success', 'تم إضافة الباقة بنجاح');
}


    public function edit(Module $module)
    {
        return view('admin.views.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug,' . $module->id,
        ]);

        $module->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->route('admin.modules.index')->with('success', 'تم تحديث الموديول بنجاح');
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('admin.modules.index')->with('success', 'تم حذف الموديول بنجاح');
    }
}
