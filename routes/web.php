<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\WalletProviderController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTransactionController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// الصفحة الترحيبية
Route::get('/', function () {
    return redirect()->route('login');
});

// المصادقة
Auth::routes();

// الصفحة الرئيسية
Route::get('/home', [DashboardController::class, 'index'])->name('home');

// تسجيل الخروج
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});

// لوحة التحكم
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // الفروع
    Route::resource('branches', BranchController::class);

    // التصنيفات
    Route::resource('categories', CategoryController::class);


    // العلامات التجارية
    Route::resource('brands', BrandController::class);

    // المنتجات
    Route::resource('products', ProductController::class);
    Route::post('products/bulk', [ProductController::class, 'bulkAction'])->name('products.bulk');


    // العملاء
    Route::resource('customers', CustomerController::class);

    // المبيعات
    Route::resource('sales', SaleController::class);

    // الإعدادات
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // الصيانة
    Route::get('/repairs/products-by-category/{id}', [RepairController::class, 'getProductsByCategory'])->name('repairs.products-by-category');
    Route::resource('repairs', RepairController::class);
    Route::get('repairs/{id}/payment', [RepairController::class, 'showPaymentForm'])->name('repairs.payments.create');
    Route::post('repairs/{id}/payment', [RepairController::class, 'storePayment'])->name('repairs.payments.store');
    Route::post('/repairs/update-status', [RepairController::class, 'updateStatus'])->name('repairs.updateStatus');

    // الموردين
    Route::resource('suppliers', SupplierController::class);

    // المشتريات
    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases/{purchase}/show', [PurchaseController::class, 'show'])->name('purchases.show');

    // المصروفات
    Route::resource('expenses', ExpenseController::class);

    // التقارير
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
    Route::get('/reports/repairs', [ReportController::class, 'repairsReport'])->name('reports.repairs');

    // نقطة البيع (POS)
    Route::get('/pos', [POSController::class, 'index'])->name('pos');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    // مزودي المحافظ
    Route::resource('wallet_providers', WalletProviderController::class);

    // المحافظ
    Route::resource('wallets', WalletController::class);

    // المعاملات
    Route::resource('wallet_transactions', WalletTransactionController::class);

});
