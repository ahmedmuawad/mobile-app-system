<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product; // تأكد من استيراد الموديل

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // العلاقة مع المنتجات
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
