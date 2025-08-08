<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;

class SetCompanyContext
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->company_id) {
                Company::setCurrentCompany(Company::find($user->company_id));
            }
        }

        return $next($request);
    }
}
