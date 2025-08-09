<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'max_users', 'max_branches','price'];

    public function modules()
    {
    return $this->belongsToMany(\App\Models\Module::class, 'module_package', 'package_id', 'module_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'package_id');
    }
}
