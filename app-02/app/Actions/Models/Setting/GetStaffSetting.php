<?php

namespace App\Actions\Models\Setting;

use App\Models\Scopes\ByStaff;
use App\Models\Setting;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

class GetStaffSetting extends GetSetting
{
    protected function completeSettingQuery()
    {
        /** @var Staff $staff */
        $staff = $this->model;
        $this->settingQuery->with([
            'settingStaffs' => function($query) use ($staff) {
                $query->scoped(new ByStaff($staff));
            },
        ]);
    }

    protected function getRelationship(Setting $setting): Collection
    {
        return $setting->settingStaffs;
    }
}
