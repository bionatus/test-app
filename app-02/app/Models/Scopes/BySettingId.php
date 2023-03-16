<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BySettingId implements Scope
{
    private int $settingId;

    public function __construct(int $settingId)
    {
        $this->settingId = $settingId;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('setting_id', $this->settingId);
    }
}
