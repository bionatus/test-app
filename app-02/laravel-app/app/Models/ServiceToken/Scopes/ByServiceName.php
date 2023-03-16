<?php

namespace App\Models\ServiceToken\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByServiceName implements Scope
{
    private string $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('service_name', '=', $this->serviceName);
    }
}
