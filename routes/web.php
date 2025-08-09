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
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PackageModuleController;
use App\Http\Controllers\CompanyController;
use App\Models\Company;
use App\Http\Controllers\StockAlertController;

// ✅ صفحة تجريبية لاختبار الاشتراك والموديولز
Route::get("/subscription-test", function () {
    $company = Company::with("package.modules")->find(1); // تجربة للشركة رقم 1
    return view("subscription-test", compact("company"));
});

// ✅ الصفحة الترحيبية
Route::get('/', function () {
    return redirect()->route('login');
});

// ✅ المصادقة
Auth::routes();

// ✅ الصفحة الرئيسية
Route::get('/home', [DashboardController::class, 'index'])->name('home');

// ✅ تسجيل الخروج
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});

// ✅ لوحة التحكم
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {

    // ✅ Dashboard
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // ✅ الفروع
    Route::middleware(['set.company','module:branches'])->group(function () {
        Route::resource('branches', BranchController::class);
    });

    // ✅ التصنيفات
    Route::middleware(['set.company','module:categories'])->group(function () {
        Route::resource('categories', CategoryController::class);
    });

    // ✅ العلامات التجارية
    Route::middleware(['set.company','module:brands'])->group(function () {
        Route::resource('brands', BrandController::class);
    });

    // ✅ المنتجات
    Route::middleware(['set.company','module:products'])->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('products/bulk', [ProductController::class, 'bulkAction'])->name('products.bulk');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    });

    // ✅ العملاء
    Route::middleware(['set.company','module:customers'])->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::get('customers/{customer}/history', [\App\Http\Controllers\CustomerController::class, 'history'])
        ->name('customers.history');
        Route::get('customers/{id}/history/export', [CustomerController::class, 'exportHistory'])
    ->name('customers.history.export');

    });

    // ✅ المبيعات
    Route::middleware(['set.company','module:sales'])->group(function () {
        Route::resource('sales', SaleController::class);
        Route::delete('sales/bulk-delete', [SaleController::class, 'bulkDelete'])->name('sales.bulkDelete');
        Route::resource('payment-methods', \App\Http\Controllers\Admin\PaymentMethodController::class);

    });

    // ✅ الإعدادات
    Route::middleware(['set.company','module:settings'])->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // ✅ الصيانة
    Route::middleware(['set.company','module:repairs'])->group(function () {
        Route::get('/repairs/products-by-category/{id}', [RepairController::class, 'getProductsByCategory'])->name('repairs.products-by-category');
        Route::resource('repairs', RepairController::class);
        Route::get('repairs/{id}/payment', [RepairController::class, 'showPaymentForm'])->name('repairs.payments.create');
        Route::post('repairs/{id}/payment', [RepairController::class, 'storePayment'])->name('repairs.payments.store');
        Route::post('/repairs/update-status', [RepairController::class, 'updateStatus'])->name('repairs.updateStatus');
        Route::post('admin/repairs/{repair}/payments', [RepairController::class, 'storePayment'])->name('admin.repairs.payments.store');
    });

    // ✅ الموردين
    Route::middleware(['set.company','module:suppliers'])->group(function () {
        Route::resource('suppliers', SupplierController::class);
    });

    // ✅ المشتريات
    Route::middleware(['set.company','module:purchases'])->group(function () {
        Route::resource('purchases', PurchaseController::class);
        Route::get('purchases/{purchase}/show', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::post('purchases/{purchase}/payments', [PurchaseController::class, 'storePayment'])->name('purchases.payments.store');
    });

    // ✅ المصروفات
    Route::middleware(['set.company','module:expenses'])->group(function () {
        Route::resource('expenses', ExpenseController::class);
    });

    // ✅ التقارير
    Route::middleware(['set.company','module:reports'])->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
        Route::get('/reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
        Route::get('/reports/repairs', [ReportController::class, 'repairsReport'])->name('reports.repairs');
    });

    // ✅ نقطة البيع (POS)
    Route::middleware(['set.company','module:pos'])->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('pos');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    });

    // ✅ مزودي المحافظ
    Route::middleware(['set.company','module:wallet_providers'])->group(function () {
        Route::resource('wallet_providers', WalletProviderController::class);
    });

    // ✅ المحافظ
    Route::middleware(['set.company','module:wallets'])->group(function () {
        Route::resource('wallets', WalletController::class);
        Route::post('wallets/deposit', [WalletController::class, 'storeDeposit'])->name('wallets.deposit');
    });

    // ✅ المعاملات
    Route::middleware(['set.company','module:wallet_transactions'])->group(function () {
        Route::resource('wallet_transactions', WalletTransactionController::class);
    });

    // ✅ اختيار الفرع
    Route::post('/change-branch', function (\Illuminate\Http\Request $request) {
        session(['current_branch_id' => $request->branch_id]);
        return back();
    })->name('change-branch');

    Route::get('change-branch/{id}', [BranchController::class, 'changeBranch'])->name('change.branch');

    // ✅ إدارة الباقات

    Route::middleware(['set.company','module:packages'])->group(function () {
    Route::resource('packages', PackageController::class);
    });

    // ✅ إدارة الموديولز
    Route::middleware(['set.company','module:modules'])->group(function () {
    Route::resource('modules', ModuleController::class);
    });
    // ✅ إدارة الاشتراكات
    Route::middleware(['set.company','module:subscriptions'])->group(function () {
    Route::resource('subscriptions', SubscriptionController::class);
    });

    // ✅ ربط الباقة بالموديولز
    Route::middleware(['set.company','module:package_modules'])->group(function () {
        Route::get('packages/{id}/modules', [PackageModuleController::class, 'edit'])->name('packages.modules.edit');
        Route::post('packages/{id}/modules', [PackageModuleController::class, 'update'])->name('packages.modules.update');
    });

    // ✅ إدارة الشركات
    Route::resource('companies', CompanyController::class);


    // طرق الدفع
    Route::resource('payment-methods', \App\Http\Controllers\Admin\PaymentMethodController::class);

    // Stock Alerts
    Route::get('stock-alerts', [\App\Http\Controllers\StockAlertController::class, 'index'])
        ->name('stock_alerts.index');

    Route::patch('stock-alerts/{id}/toggle', [\App\Http\Controllers\StockAlertController::class, 'toggleStatus'])
        ->name('stock_alerts.toggle');

});
