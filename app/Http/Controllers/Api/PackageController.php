<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Module;

class PackageController extends Controller
{
    public function index()
    {
        // جلب جميع الباقات (النشطة) مع الموديولات المرتبطة
        $packages = Package::orderBy('price')->get()->map(function ($p) {
            $modules = $p->modules()->get()->map(function ($m) {
                return [
                    'id' => $m->id,
                    'slug' => $m->slug,
                    'name' => $m->name,
                ];
            });

            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'max_users' => (int) $p->max_users,
                'max_branches' => (int) $p->max_branches,
                'duration_days' => isset($p->duration_days) ? (int)$p->duration_days : 30,
                'modules' => $modules,
            ];
        });

        return response()->json($packages);
    }

    public function show($id)
    {
        $p = Package::findOrFail($id);
        $modules = $p->modules()->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'slug' => $m->slug,
                'name' => $m->name,
            ];
        });

        return response()->json([
            'id' => $p->id,
            'name' => $p->name,
            'price' => (float) $p->price,
            'max_users' => (int) $p->max_users,
            'max_branches' => (int) $p->max_branches,
            'duration_days' => isset($p->duration_days) ? (int)$p->duration_days : 30,
            'modules' => $modules,
        ]);
    }
}
