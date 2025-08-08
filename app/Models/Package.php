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
        return $this->belongsToMany(Module::class, "module_package");
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'package_id');
    }
}
