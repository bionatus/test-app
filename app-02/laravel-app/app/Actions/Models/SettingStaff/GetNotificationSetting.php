<?php

namespace App\Actions\Models\SettingStaff;

use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByStaff;
use App\Models\Setting;
use App\Models\Staff;

class GetNotificationSetting
{
    private Staff  $staff;
    private string $slug;

    public function __construct(Staff $staff, string $slug)
    {
        $this->staff = $staff;
        $this->slug  = $slug;
    }

    public function execute(): bool
    {
        $staff         = $this->staff;
        $setting       = Setting::scoped(new ByRouteKey($this->slug))->with([
            'settingStaffs' => function($query) use ($staff) {
                $query->scoped(new ByStaff($staff));
            },
        ])->first();
        $settingStaffs = $setting->settingStaffs;

        return $settingStaffs->isNotEmpty() ? $settingStaffs->first()->value : $setting->value;
    }
}
