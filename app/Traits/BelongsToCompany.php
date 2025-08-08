<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait BelongsToCompany
{
    public static function bootBelongsToCompany()
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
