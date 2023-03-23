<?php

namespace App\Models\Agent\Scopes;

use App\Models\Scopes\ByRouteKey;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Available implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('user', function(Builder $builder) {
            $builder->whereHas('settingUsers', function(Builder $builder) {
                $builder->whereHas('setting', function(Builder $builder) {
                    $builder->scoped(new ByRouteKey(Setting::SLUG_AGENT_AVAILABLE));
                });
                $builder->where('value', true);
            });
        });
    }
}
