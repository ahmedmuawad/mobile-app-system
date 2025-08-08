<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckModule
{
    public function handle($request, Closure $next, $moduleName)
    {
        $user = Auth::user();

        // سوبر أدمن يشوف كل حاجة
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $company = $user->company;

        if (!$company || !$company->package || !$company->package->modules->contains('slug', $moduleName)) {
            abort(403, 'غير مسموح لك بالوصول لهذا الموديول.');
        }

        return $next($request);
    }
}
