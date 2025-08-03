<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'subdomain', 'email', 'phone', 'logo',
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
}
