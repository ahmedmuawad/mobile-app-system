<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'subdomain', 'billing_email', 'phone', 'logo',
        'address', 'package_id', 'subscription_ends_at',
        'trial_ends_at', 'max_users', 'max_branches',
        'locale', 'is_active'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // إرجاع الاشتراك النشط (أحدث اشتراك بحالة 'active')
    public function activeSubscription()
    {
        return $this->subscriptions()->where('status', 'active')->orderByDesc('ends_at')->first();
    }

    // يفحص إذا الباقة الحالية للشركة تملك موديول معين
    public function hasModule(string $moduleSlug): bool
    {
        $sub = $this->activeSubscription();
        if (! $sub) {
            return false;
        }
        return $sub->package->modules()->where('slug', $moduleSlug)->exists();
    }

    // علاقة مباشرة للموديولات عبر الباقة (اختياري)
    public function modules()
    {
        return $this->package ? $this->package->modules() : collect();
    }

    // scope لاختيار الشركات النشطة فقط
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper لتحديث حالة الشركة عند تغيير الاشتراك
    public function applySubscription(Subscription $subscription)
    {
        $this->package_id = $subscription->package_id;
        $this->subscription_ends_at = $subscription->ends_at;

        // مثال: تفعيل الشركة إذا الاشتراك مفعل
        if ($subscription->status === 'active') {
            $this->is_active = true;
        } else {
            $this->is_active = false;
        }

        // ممكن تضيف هنا تحديث صلاحيات المستخدمين أو غيرها حسب الحاجة

        $this->save();
    }

    protected static $currentCompany = null;

    public static function setCurrentCompany(?Company $company)
    {
        self::$currentCompany = $company;
    }

    public static function current(): ?Company
    {
        return self::$currentCompany;
    }
}
