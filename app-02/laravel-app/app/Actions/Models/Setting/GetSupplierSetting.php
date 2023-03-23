<?php

namespace App\Actions\Models\Setting;

use App\Models\Scopes\BySupplier;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class GetSupplierSetting extends GetSetting
{
    protected function completeSettingQuery()
    {
        /** @var Supplier $supplier */
        $supplier = $this->model;
        $this->settingQuery->with([
            'settingSuppliers' => function($query) use ($supplier) {
                $query->scoped(new BySupplier($supplier));
            },
        ]);
    }

    protected function getRelationship(Setting $setting): Collection
    {
        return $setting->settingSuppliers;
    }
}
