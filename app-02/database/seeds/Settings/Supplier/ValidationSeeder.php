<?php

namespace Database\Seeders\Settings\Supplier;

use App\Constants\Environments;
use App\Models\Setting;
use App\Models\Supplier;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\SeedsEnvironment;
use Illuminate\Database\Seeder;

class ValidationSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SETTINGS = [
        Setting::SLUG_BID_NUMBER_REQUIRED => [
            'name'          => "Bid Number Required",
            'slug'          => Setting::SLUG_BID_NUMBER_REQUIRED,
            'group'         => Setting::GROUP_VALIDATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
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
