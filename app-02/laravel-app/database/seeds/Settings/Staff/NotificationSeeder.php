<?php

namespace Database\Seeders\Settings\Staff;

use App\Constants\Environments;
use App\Models\Setting;
use App\Models\Staff;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\SeedsEnvironment;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SETTINGS = [
        Setting::SLUG_STAFF_EMAIL_NOTIFICATION => [
            'name'          => "Staff Email Notifications",
            'slug'          => Setting::SLUG_STAFF_EMAIL_NOTIFICATION,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Staff::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
        Setting::SLUG_STAFF_SMS_NOTIFICATION   => [
            'name'          => "Staff Sms notifications",
            'slug'          => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Staff::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
    ];

    public function run()
    {
        foreach (self::SETTINGS as $slug => $settingData) {
            Setting::updateOrCreate(['slug' => $slug], $settingData);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
