<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = 'login|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many login attempts. Try again later.'
            ], 429);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey, 60); // lock for 60s per attempt
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        RateLimiter::clear($throttleKey);

        $user = Auth::user();
        $company = $user->company ?? null;

        // أنشئ توكن (يعمل مع Laravel Sanctum)
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'company' => $company,
                'company_id' => $company->id ?? null,
                'token' => $token
            ]
        ]);
    }
}
