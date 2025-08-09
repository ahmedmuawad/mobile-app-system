<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Package;
use App\Models\Subscription;
use Carbon\Carbon;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Create a payment session (Stripe Checkout example or WooCommerce order creation)
     * Body:
     * {
     *   company_id: int,
     *   package_id: int,
     *   provider: 'stripe'|'woocommerce' (optional, default stripe),
     *   success_url: string (optional),
     *   cancel_url: string (optional)
     * }
     */
    public function createSession(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:packages,id',
            'provider' => 'nullable|string|in:stripe,woocommerce',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        $company = Company::findOrFail($data['company_id']);
        $package = Package::findOrFail($data['package_id']);

        if ((float)$package->price <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Package is free. No payment needed.'], 400);
        }

        $provider = $data['provider'] ?? 'stripe';

        if ($provider === 'stripe') {
            $stripeSecret = env('STRIPE_SECRET');
            if (!$stripeSecret) {
                return response()->json(['status' => 'error', 'message' => 'Stripe secret not configured.'], 500);
            }

            $stripe = new StripeClient($stripeSecret);

            $successUrl = $data['success_url'] ?? url('/payment/success?session_id={CHECKOUT_SESSION_ID}');
            $cancelUrl = $data['cancel_url'] ?? url('/payment/cancel');

            try {
                $session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'mode' => 'payment',
                    'line_items' => [[
                        'price_data' => [
                            'currency' => env('PAYMENT_CURRENCY', 'usd'),
                            'product_data' => [
                                'name' => $package->name,
                            ],
                            'unit_amount' => (int)round($package->price * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'client_reference_id' => $company->id,
                    'metadata' => [
                        'company_id' => $company->id,
                        'package_id' => $package->id,
                        'provider' => 'stripe'
                    ],
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'checkout_url' => $session->url,
                        'session_id' => $session->id,
                        'provider' => 'stripe'
                    ]
                ]);
            } catch (\Exception $e) {
                Log::error('Stripe create session error: '.$e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Failed to create payment session', 'error' => $e->getMessage()], 500);
            }
        }

        // provider = woocommerce
        if ($provider === 'woocommerce') {
            // إعدادات WooCommerce من env
            $wcUrl = env('WOOC_URL');
            $wcKey = env('WOOC_KEY');
            $wcSecret = env('WOOC_SECRET');

            if (!$wcUrl || !$wcKey || !$wcSecret) {
                return response()->json(['status' => 'error', 'message' => 'WooCommerce credentials not configured.'], 500);
            }

            // بناء الطلب لمتجر WooCommerce — هذا مثال عام ويجب تفصيله بحسب بوابة الدفع والـ plugins المستخدمة في وووكومرس
            try {
                // مثال: ننشئ order بسيط عبر WooCommerce REST API (دعم Basic Auth query params)
                $orderPayload = [
                    'payment_method' => 'bacs', // عدّل حسب بوابة الدفع المتاحة في WooCommerce
                    'payment_method_title' => 'Manual (WooCommerce Checkout)',
                    'set_paid' => false,
                    'billing' => [
                        'email' => $company->billing_email ?? null,
                        'first_name' => $company->name,
                    ],
                    'line_items' => [
                        [
                            'name' => $package->name,
                            'product_id' => null,
                            'quantity' => 1,
                            'total' => number_format($package->price, 2, '.', '')
                        ]
                    ],
                    'meta_data' => [
                        ['key' => 'company_id', 'value' => $company->id],
                        ['key' => 'package_id', 'value' => $package->id],
                        ['key' => 'provider', 'value' => 'woocommerce'],
                    ]
                ];

                // نستخدم Http client (Laravel) مع Basic Auth عبر query params كما تطلب بعض متاجر WooCommerce
                $endpoint = rtrim($wcUrl, '/') . '/wp-json/wc/v3/orders';
                $response = Http::withBasicAuth($wcKey, $wcSecret)
                    ->post($endpoint, $orderPayload);

                if ($response->successful()) {
                    $body = $response->json();
                    // عادة WooCommerce يرجع رابط الدفع أو تفاصيل الدفع في response (يعتمد على إعدادات المتجر)
                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'provider' => 'woocommerce',
                            'order' => $body
                        ]
                    ]);
                } else {
                    Log::error('WooCommerce order creation failed: '.$response->body());
                    return response()->json(['status' => 'error', 'message' => 'Failed to create WooCommerce order', 'error' => $response->body()], 500);
                }
            } catch (\Exception $e) {
                Log::error('WooCommerce create order error: '.$e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Failed to create WooCommerce order', 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Unsupported provider.'], 400);
    }

    /**
     * Webhook handler for payment providers
     * - Stripe: handle checkout.session.completed
     * - WooCommerce: depends on WooCommerce plugin webhook payload (order.completed / order.updated)
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $provider = $request->header('X-Payment-Provider') ?? $request->input('provider') ?? null;

        // Try to detect stripe if stripe-signature header exists
        if (!$provider && $request->header('Stripe-Signature')) {
            $provider = 'stripe';
        }

        try {
            if ($provider === 'stripe') {
                $sigHeader = $request->header('Stripe-Signature');
                $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

                if (!$endpointSecret) {
                    Log::error('Stripe webhook secret not set.');
                    return response()->json(['status' => 'error', 'message' => 'Stripe webhook not configured.'], 500);
                }

                // verify and construct event
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
                $type = $event->type ?? null;

                if ($type === 'checkout.session.completed') {
                    $session = $event->data->object;
                    $companyId = $session->metadata->company_id ?? ($session->client_reference_id ?? null);
                    $packageId = $session->metadata->package_id ?? null;
                    $providerSubscriptionId = $session->id ?? null;

                    if ($companyId && $packageId) {
                        $this->activateSubscriptionFromProvider((int)$companyId, (int)$packageId, 'stripe', $providerSubscriptionId);
                    }
                }
                return response()->json(['status' => 'success', 'received' => true]);
            }

            if ($provider === 'woocommerce' || $request->isJson() || $request->header('Content-Type') === 'application/json') {
                // WooCommerce webhook payload example: order.updated or order.completed
                $body = $request->json()->all();

                // محاولة استخراج metadata من payload
                $meta = $body['meta_data'] ?? $body['meta'] ?? [];
                $companyId = null;
                $packageId = null;

                // بعض متاجر WooCommerce ترسل meta_data كمصفوفة من كائنات
                if (is_array($meta)) {
                    foreach ($meta as $m) {
                        if ((isset($m['key']) && $m['key'] === 'company_id') || (isset($m['key']) && $m['key'] === '_company_id')) {
                            $companyId = $m['value'];
                        }
                        if ((isset($m['key']) && $m['key'] === 'package_id') || (isset($m['key']) && $m['key'] === '_package_id')) {
                            $packageId = $m['value'];
                        }
                    }
                }

                // بعض إعدادات وووكومرس ترسل custom_fields أو متغيرات أخرى — هنا تحتاج تكييف حسب المتجر
                // نتحقق أيضاً من حالة الدفع (مثل paid, completed)
                $status = $body['status'] ?? $body['payment_status'] ?? $body['order_status'] ?? null;
                $isPaid = false;
                if (in_array(strtolower($status), ['processing', 'completed', 'paid'])) {
                    $isPaid = true;
                }
                // أيضاً بعض الـ plugins يرسلون "set_paid": true في webhook
                if (isset($body['set_paid']) && $body['set_paid'] == true) {
                    $isPaid = true;
                }

                if ($isPaid && $companyId && $packageId) {
                    $this->activateSubscriptionFromProvider((int)$companyId, (int)$packageId, 'woocommerce', $body['id'] ?? null);
                }

                return response()->json(['status' => 'success', 'received' => true]);
            }

            // If provider not detected: log
            Log::warning('Unknown payment webhook received', ['headers' => $request->headers->all()]);
            return response()->json(['status' => 'error', 'message' => 'Unknown provider or payload.'], 400);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook error: '.$e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Webhook processing error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * موحّد: يفعل/ينشئ الاشتراك القادم من مزوّد دفع
     */
    protected function activateSubscriptionFromProvider(int $companyId, int $packageId, string $provider, $providerReference = null)
    {
        try {
            $package = Package::find($packageId);
            if (!$package) {
                Log::warning('Package not found for provider activation', ['package_id' => $packageId]);
                return false;
            }

            $company = Company::find($companyId);
            if (!$company) {
                Log::warning('Company not found for provider activation', ['company_id' => $companyId]);
                return false;
            }

            // تجنب التفعيل المكرر: تحقق من عدم وجود subscription بنفس providerReference
            if ($providerReference) {
                $exists = Subscription::where('provider', $provider)
                    ->where('provider_subscription_id', $providerReference)
                    ->first();
                if ($exists) {
                    Log::info('Duplicate provider event ignored', ['provider' => $provider, 'ref' => $providerReference]);
                    return false;
                }
            }

            // بحث عن اشتراك pending أولًا
            $sub = Subscription::where('company_id', $companyId)
                ->where('package_id', $packageId)
                ->where('status', 0)
                ->latest()
                ->first();

            $starts = Carbon::now();
            $ends = Carbon::now()->addDays($package->duration_days ?? 30);

            if ($sub) {
                $sub->starts_at = $starts;
                $sub->ends_at = $ends;
                $sub->status = 1;
                $sub->provider = $provider;
                if ($providerReference) $sub->provider_subscription_id = $providerReference;
                $sub->save();
            } else {
                $sub = Subscription::create([
                    'company_id' => $companyId,
                    'package_id' => $packageId,
                    'starts_at' => $starts,
                    'ends_at' => $ends,
                    'status' => 1,
                    'provider' => $provider,
                    'provider_subscription_id' => $providerReference,
                ]);
            }

            $company->update([
                'package_id' => $packageId,
                'subscription_ends_at' => $ends,
                'is_active' => 1,
            ]);

            Log::info('Subscription activated from provider', ['company_id' => $companyId, 'package_id' => $packageId, 'provider' => $provider, 'ref' => $providerReference]);
            return true;
        } catch (\Exception $e) {
            Log::error('activateSubscriptionFromProvider error: '.$e->getMessage());
            return false;
        }
    }
}
