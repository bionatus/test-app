<?php

namespace App\Scopes;

use App\Models\Brand;
use App\Models\Scopes\ByRouteKey;
use Illuminate\Database\Query\Builder;

class ByBrandRouteKey implements Scope
{
    private ?string $brandRouteKey;

    public function __construct(?string $brandRouteKey)
    {
        $this->brandRouteKey = $brandRouteKey;
    }

    public function apply(Builder $builder): void
    {
        if ($this->brandRouteKey) {
            $brand = Brand::scoped(new ByRouteKey($this->brandRouteKey))->first();
            $builder->where('brand_id', $brand->getKey());
        }
    }
}
