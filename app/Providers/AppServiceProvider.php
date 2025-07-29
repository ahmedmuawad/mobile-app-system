<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View; // ✅ هذا السطر مهم جدًا
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // شارك الإعدادات مع جميع الـ views
        View::composer('*', function ($view) {
            $view->with('globalSetting', Setting::first());
        });
    }
}
