<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * تسجيل شركة + مستخدم إداري (مع اختيار باقة اختياري)
     * Body:
     * {
     *  company: { name, subdomain, billing_email, phone },
     *  user: { name, email, password, password_confirmation },
     *  package_id: optional,
     *  payment_method: optional
     * }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'company.name' => 'required|string|max:255',
            'company.subdomain' => 'required|string|max:255|alpha_dash|unique:companies,subdomain',
            'company.billing_email' => 'nullable|email|max:255',
            'company.phone' => 'nullable|string|max:50',
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|confirmed|min:6',
            'package_id' => 'nullable|exists:packages,id',
            'payment_method' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $companyData = $data['company'];
            $company = Company::create([
                'name' => $companyData['name'],
                'subdomain' => $companyData['subdomain'],
                'billing_email' => $companyData['billing_email'] ?? null,
                'phone' => $companyData['phone'] ?? null,
                'is_active' => 0,
            ]);

            $userData = $data['user'];
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'company_admin',
                'company_id' => $company->id,
            ]);

            // لو المستخدم اختار باقة مباشرة
            if (!empty($data['package_id'])) {
                $package = Package::findOrFail($data['package_id']);
                if ((float)$package->price <= 0) {
                    // باقة مجانية -> تفعيل فورى
                    $starts = Carbon::now();
                    $ends = Carbon::now()->addDays($package->duration_days ?? 30);
                    $subscription = Subscription::create([
                        'company_id' => $company->id,
                        'package_id' => $package->id,
                        'starts_at' => $starts,
                        'ends_at' => $ends,
                        'status' => 1, // active
                        'auto_renew' => 0,
                    ]);
                    $company->update([
                        'package_id' => $package->id,
                        'subscription_ends_at' => $ends,
                        'is_active' => 1,
                    ]);
                    DB::commit();
                    $token = $user->createToken('api-token')->plainTextToken;
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Registration successful. Subscription active.',
                        'data' => [
                            'token' => $token,
                            'company' => $company,
                            'subscription' => $subscription
                        ]
                    ], 201);
                } else {
                    // باقة مدفوعة -> أنشئ اشتراك pending (status = 0)
                    $subscription = Subscription::create([
                        'company_id' => $company->id,
                        'package_id' => $package->id,
                        'status' => 0, // pending
                        'auto_renew' => 0,
                    ]);
                    DB::commit();

                    // نرجع next steps للفرونت / البلجن ليكمل الدفع
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Registration created. Pending payment.',
                        'data' => [
                            'company_id' => $company->id,
                            'package_id' => $package->id,
                            'subscription_id' => $subscription->id,
                            // front should call PaymentController@createSession or your WP plugin integration
                            'next' => url('/api/payments/create')
                        ]
                    ], 202);
                }
            }

            DB::commit();
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful (no package chosen).',
                'data' => [
                    'token' => $token,
                    'company' => $company
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Register error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
