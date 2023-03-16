<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SETTINGS = [
        AppSetting::SLUG_SEARCH_BY_PART_IFRAME_URL   => [
            'slug'  => AppSetting::SLUG_SEARCH_BY_PART_IFRAME_URL,
            'label' => 'Search by part iframe url',
            'type'  => AppSetting::TYPE_STRING,
        ],
        AppSetting::SLUG_BLUON_POINTS_MULTIPLIER     => [
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'label' => "Bluon Points Multiplier",
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 4,
        ],
        AppSetting::SLUG_TECHNICIAN_ONBOARDING_VIDEO => [
            'slug'  => AppSetting::SLUG_TECHNICIAN_ONBOARDING_VIDEO,
            'label' => "Technician Onboarding Video",
            'type'  => AppSetting::TYPE_STRING,
        ],
        AppSetting::SLUG_HOME_SCREEN_VIDEO           => [
            'slug'  => AppSetting::SLUG_HOME_SCREEN_VIDEO,
            'label' => "Home Screen Video",
            'type'  => AppSetting::TYPE_STRING,
        ],
        AppSetting::SLUG_TUTORIAL_VIDEO              => [
            'slug'  => AppSetting::SLUG_TUTORIAL_VIDEO,
            'label' => "Tutorial Video",
            'type'  => AppSetting::TYPE_STRING,
        ],
    ];

    public function run()
    {
        foreach (self::SETTINGS as $slug => $settingData) {
            AppSetting::updateOrCreate(['slug' => $slug], $settingData);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
