<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * إنشاء اشتراك جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'auto_renew'     => 'boolean',
            'payment_method' => 'nullable|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json(['status' => 'error', 'message' => 'User has no company.'], 422);
        }

        $package = Package::findOrFail($request->package_id);

        DB::beginTransaction();
        try {
            // منع إنشاء اشتراك مكرر لنفس الشركة و نفس الباقة إذا كانت موجودة وحالتها pending أو active
            $existing = Subscription::where('company_id', $company->id)
                ->where('package_id', $package->id)
                ->whereIn('status', [0, 1])
                ->latest()
                ->first();

            if ($existing) {
                // إذا كانت active و ما انتهتش — نُبلغ المستخدم
                if ($existing->status == 1 && isset($existing->ends_at) && $existing->ends_at > now()) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'An active subscription for this package already exists.',
                        'data' => ['subscription_id' => $existing->id]
                    ], 409);
                }

                // إذا كانت pending — نُعيد رسالة لتوجيه الدفع
                if ($existing->status == 0) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'There is already a pending subscription for this package. Proceed to payment.',
                        'data' => ['subscription_id' => $existing->id, 'payment_url' => url('/api/payments/create')]
                    ], 409);
                }
            }

            if ((float)$package->price <= 0) {
                // باقة مجانية → تفعيل مباشر
                $starts = now();
                $ends   = now()->addDays($package->duration_days ?? 30);

                $sub = Subscription::create([
                    'company_id' => $company->id,
                    'package_id' => $package->id,
                    'starts_at'  => $starts,
                    'ends_at'    => $ends,
                    'status'     => 1, // active
                    'auto_renew' => $request->auto_renew ? 1 : 0,
                ]);

                $company->update([
                    'package_id'           => $package->id,
                    'subscription_ends_at' => $ends,
                    'is_active'            => 1,
                ]);

                DB::commit();
                return response()->json(['status' => 'success', 'data' => $sub], 201);
            }

            // باقة مدفوعة → Pending + تجهيز الدفع
            $sub = Subscription::create([
                'company_id' => $company->id,
                'package_id' => $package->id,
                'status'     => 0, // pending
                'auto_renew' => $request->auto_renew ? 1 : 0,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Subscription created. Proceed to payment.',
                'data' => [
                    'subscription_id' => $sub->id,
                    'payment_url'     => url('/api/payments/create')
                ]
            ], 202);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription store error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subscription.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض الاشتراك الحالي للمستخدم
     */
    public function currentByAuth(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->company) {
            return response()->json(['status' => 'error', 'message' => 'No company associated with authenticated user'], 422);
        }

        return $this->getCurrentSubscription($user->company->id);
    }

    /**
     * عرض الاشتراك الحالي برقم الشركة
     * هذه الدالة قد تكون عامة (مسموح للبلجن/زوار الاطلاع على باقة الشركة) لذا نعطي بيانات عامة.
     */
    public function currentByCompanyId($company_id)
    {
        return $this->getCurrentSubscription($company_id);
    }

    /**
     * تفعيل الاشتراك بعد الدفع
     */
    public function activate(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:packages,id'
        ]);

        // Authorization: only company owner/admin or super_admin allowed
        $auth = Auth::user();
        if ($auth && !$this->isAuthorizedForCompany($auth, (int)$request->company_id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $company = Company::findOrFail($request->company_id);
        $package = Package::findOrFail($request->package_id);

        DB::beginTransaction();
        try {
            $starts = now();
            $ends   = now()->addDays($package->duration_days ?? 30);

            // حاولنا البحث عن اشتراك pending أولًا لأن غالبًا سيتم تفعيله
            $subscription = Subscription::where('company_id', $company->id)
                ->where('package_id', $package->id)
                ->orderBy('ends_at', 'desc')
                ->first();

            if ($subscription) {
                $subscription->starts_at = $starts;
                $subscription->ends_at = $ends;
                $subscription->status = 1;
                $subscription->save();
            } else {
                $subscription = Subscription::create([
                    'company_id' => $company->id,
                    'package_id' => $package->id,
                    'starts_at'  => $starts,
                    'ends_at'    => $ends,
                    'status'     => 1
                ]);
            }

            // تحديث شركة: لاحظ أننا نخزن الباقة الحالية ووقت الانتهاء
            $company->update([
                'package_id' => $package->id,
                'subscription_ends_at' => $ends,
                'is_active' => 1,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Subscription activated successfully.',
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription activate error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate subscription.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تجديد الاشتراك
     */
    public function renew(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $auth = Auth::user();
        if ($auth && !$this->isAuthorizedForCompany($auth, (int)$request->company_id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $company = Company::findOrFail($request->company_id);
        $subscription = Subscription::where('company_id', $company->id)
            ->orderBy('ends_at', 'desc')
            ->first();

        if (!$subscription) {
            return response()->json(['status' => 'error', 'message' => 'No active subscription found'], 404);
        }

        try {
            // أضف مدة الباقة إلى النهاية الحالية (أو من الآن لو انتهت)
            $base = Carbon::parse($subscription->ends_at);
            if ($base->lt(now())) {
                $base = now();
            }

            $daysToAdd = $subscription->package->duration_days ?? 30;
            $ends = $base->addDays($daysToAdd);

            $subscription->ends_at = $ends;
            $subscription->save();

            $company->update(['subscription_ends_at' => $ends]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription renewed successfully',
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            Log::error('Subscription renew error: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to renew subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إلغاء الاشتراك
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $auth = Auth::user();
        if ($auth && !$this->isAuthorizedForCompany($auth, (int)$request->company_id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $subscription = Subscription::where('company_id', $request->company_id)
            ->orderBy('ends_at', 'desc')
            ->first();

        if (!$subscription) {
            return response()->json(['status' => 'error', 'message' => 'No subscription found'], 404);
        }

        try {
            $subscription->status = 0; // cancelled/pending
            $subscription->save();

            // نحدّث حالة الشركة فقط إذا هذه هي الباقة النشطة
            $company = $subscription->company;
            if ($company) {
                // إنقاص حالة التفعيل للشركة — احتياطياً يمكن تعديل هذا السلوك حسب سياسة العمل
                $company->is_active = 0;
                $company->save();
            }

            return response()->json(['status' => 'success', 'message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            Log::error('Subscription cancel error: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to cancel subscription', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * عرض تاريخ الاشتراكات
     */
    public function history($company_id)
    {
        // history قد تُطلب من إدارة أو من صاحب الشركة — لو فيه توثيق middleware فحسّن ما يلزم
        $auth = Auth::user();
        if ($auth && !$this->isAuthorizedForCompany($auth, (int)$company_id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $subscriptions = Subscription::with('package')
            ->where('company_id', $company_id)
            ->orderBy('starts_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $subscriptions]);
    }

    /**
     * دالة خاصة لإرجاع الاشتراك الحالي
     */
    private function getCurrentSubscription($company_id)
    {
        if (!$company_id) {
            return response()->json(['status' => 'error', 'message' => 'No company ID provided'], 422);
        }

        $subscription = Subscription::with('package')
            ->where('company_id', $company_id)
            ->orderBy('ends_at', 'desc')
            ->first();

        if (!$subscription) {
            return response()->json(['status' => 'error', 'message' => 'No subscription found'], 404);
        }

        // إرجاع بيانات أكثر لتمكين البلجن من عرض التفاصيل دون طلب أكثر
        return response()->json([
            'status' => 'success',
            'data' => [
                'id'            => $subscription->id,
                'package_id'    => $subscription->package_id,
                'package_name'  => $subscription->package->name ?? null,
                'price'         => $subscription->package->price ?? null,
                'status'        => $subscription->status == 1 ? 'active' : 'pending',
                'starts_at'     => $subscription->starts_at,
                'ends_at'       => $subscription->ends_at,
                'duration_days' => $subscription->package->duration_days ?? null,
                'auto_renew'    => (bool) $subscription->auto_renew
            ]
        ]);
    }

    /**
     * Helper: تحقق صلاحية المستخدم للتصرف على بيانات الشركة
     * حالياً: صاحب الشركة (user->company_id === company_id) أو دور super_admin
     */
    private function isAuthorizedForCompany($user, int $company_id): bool
    {
        try {
            if (!$user) return false;

            // إذا كان سوبر أدمن (إن وجد دور بهذا الإسم) أعطه صلاحية كاملة
            if (isset($user->role) && $user->role === 'super_admin') {
                return true;
            }

            if (isset($user->company_id) && (int)$user->company_id === $company_id) {
                return true;
            }

            // لو في علاقة متعددة للمستخدم (مثل user->companies) يمكنك توسيع هذا القسم
            return false;
        } catch (\Exception $e) {
            Log::error('Auth check error: '.$e->getMessage());
            return false;
        }
    }
}
