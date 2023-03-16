<?php

namespace App\Actions\Models\SettingSupplier;

use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\BySupplier;
use App\Models\Setting;
use App\Models\Supplier;

class GetNotificationSetting
{
    private Supplier $supplier;
    private string   $slug;

    public function __construct(Supplier $supplier, string $slug)
    {
        $this->supplier = $supplier;
        $this->slug     = $slug;
    }

    public function execute(): bool
    {
        $supplier         = $this->supplier;
        $setting          = Setting::scoped(new ByRouteKey($this->slug))->with([
            'settingSuppliers' => function($query) use ($supplier) {
                $query->scoped(new BySupplier($supplier));
            },
        ])->first();
        $settingSuppliers = $setting->settingSuppliers;

        return $settingSuppliers->isNotEmpty() ? $settingSuppliers->first()->value : $setting->value;
    }
}
