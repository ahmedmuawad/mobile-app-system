<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WooCommerceController extends Controller
{
    /**
     * استقبال بيانات الـ Webhook من WooCommerce
     */
    public function handleWebhook(Request $request)
    {
        // تسجيل البيانات في اللوج لمراجعتها
        Log::info('WooCommerce Webhook Received:', $request->all());

        // مثال: معالجة حدث طلب جديد
        $event = $request->header('X-WC-Webhook-Event');

        switch ($event) {
            case 'order.created':
                // هنا تضيف الكود اللي يتعامل مع الطلب الجديد
                Log::info('Order Created:', $request->all());
                break;

            case 'order.updated':
                // هنا الكود الخاص بتحديث الطلب
                Log::info('Order Updated:', $request->all());
                break;

            default:
                Log::warning('Unhandled WooCommerce Event: ' . $event);
                break;
        }

        // لازم نرجع 200 علشان WooCommerce يعرف إن الاستقبال نجح
        return response()->json(['status' => 'success'], 200);
    }
    public function webhook(Request $request)
    {
        Log::info('WooCommerce Webhook Received:', $request->all());

        // تحديد نوع الحدث
        $event = $request->get('event') ?? $request->get('status') ?? 'unknown';

        switch ($event) {
            case 'order.created':
            case 'created':
                Log::info('Processing new order:', ['order_id' => $request->get('id') ?? $request->get('order_id')]);
                // هنا نعمل منطق انشاء الاشتراك أو تفعيل الطلب
                break;

            case 'order.updated':
            case 'updated':
            case 'completed':
                Log::info('Processing updated/completed order:', ['order_id' => $request->get('id') ?? $request->get('order_id')]);
                // هنا منطق تحديث حالة الاشتراك أو إكمال الدفع
                break;

            default:
                Log::warning('Unhandled WooCommerce Event: ' . $event);
        }

        return response()->json(['status' => 'success']);
    }

}
