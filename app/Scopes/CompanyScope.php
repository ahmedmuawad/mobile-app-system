<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Company;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $company = Company::current();
        if ($company) {
            if (Schema::hasColumn($model->getTable(), 'company_id')) {
                $builder->where($model->getTable() . '.company_id', $company->id);
            }
        }
    }
}
