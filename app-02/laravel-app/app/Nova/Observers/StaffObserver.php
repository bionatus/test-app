<?php

namespace App\Nova\Observers;

use App;
use App\Models\Scopes\ByRouteKey;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use Illuminate\Support\Collection;

class StaffObserver
{
    public function saved(Staff $staff)
    {
        if (!$staff->isCounter()) {
            return;
        }

        $settingStaffData = [];

        if (request()->has('sms_notification')) {
            $settingStaffData[Setting::SLUG_STAFF_SMS_NOTIFICATION] = [
                'value'   => request()->get('sms_notification'),
                'setting' => Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first(),
            ];
        }

        if (request()->has('email_notification')) {
            $settingStaffData[Setting::SLUG_STAFF_EMAIL_NOTIFICATION] = [
                'value'   => request()->get('email_notification'),
                'setting' => Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first(),
            ];
        }

        Collection::make($settingStaffData)->each(function($data) use ($staff) {
            SettingStaff::updateOrCreate([
                'staff_id'   => $staff->getKey(),
                'setting_id' => $data['setting']->getKey(),
            ], ['value' => $data['value']]);
        });
    }
}
