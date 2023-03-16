<?php

namespace App\Models\Call\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByProvider implements Scope
{
    private string $provider;
    private string $providerId;

    public function __construct(string $provider, string $providerId)
    {
        $this->provider   = $provider;
        $this->providerId = $providerId;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('communication', function(Builder $builder) {
            $builder->scoped(new \App\Models\Communication\Scopes\ByProvider($this->provider, $this->providerId));
        });
    }
}
