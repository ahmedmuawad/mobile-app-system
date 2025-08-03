<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'address',
        'phone', 'is_main', 'is_active'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * علاقة Many-to-Many مع المنتجات باستخدام pivot (مع السعر والمخزون)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['price', 'stock'])
            ->withTimestamps();
    }

}
