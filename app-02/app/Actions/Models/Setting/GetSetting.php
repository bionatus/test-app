<?php

namespace App\Actions\Models\Setting;

use App\Models\HasSetting;
use App\Models\Scopes\ByRouteKey;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

abstract class GetSetting
{
    protected HasSetting $model;
    protected string     $slug;
    protected ?Builder   $settingQuery;

    public function __construct(HasSetting $model, string $slug)
    {
        $this->model = $model;
        $this->slug  = $slug;
    }

    public function execute(): bool
    {
        $this->settingQuery = Setting::scoped(new ByRouteKey($this->slug));
        $this->completeSettingQuery();
        /** @var Setting $setting */
        $setting      = $this->settingQuery->first();
        $settingModel = $this->getRelationship($setting)->first();

        return ($settingModel) ? $settingModel->value : $setting->value;
    }

    protected function completeSettingQuery()
    {
        //Implemented by subclass
    }

    protected function getRelationship(Setting $setting): Collection
    {
        //Implemented by subclass
        return Collection::make([]);
    }
}
