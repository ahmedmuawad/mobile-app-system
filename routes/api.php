<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\WooCommerceController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::post('/woocommerce/webhook', [WooCommerceController::class, 'handleWebhook']);

Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{id}', [PackageController::class, 'show']);

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/
Route::post('/payments/create', [PaymentController::class, 'createSession'])->name('api.payments.create');
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

/*
|--------------------------------------------------------------------------
| Subscriptions (public access)
|--------------------------------------------------------------------------
*/
Route::get('/my-subscription/{company_id}', [SubscriptionController::class, 'currentByCompanyId']);
Route::post('/subscriptions/activate', [SubscriptionController::class, 'activate']);

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/my-subscription', [SubscriptionController::class, 'currentByAuth']);

    Route::post('/subscriptions/renew', [SubscriptionController::class, 'renew']);
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel']);
    Route::get('/subscriptions/history/{company_id}', [SubscriptionController::class, 'history']);




});
