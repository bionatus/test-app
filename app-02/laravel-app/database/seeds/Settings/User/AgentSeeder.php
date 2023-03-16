<?php

namespace Database\Seeders\Settings\User;

use App\Constants\Environments;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\SeedsEnvironment;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SETTINGS = [
        Setting::SLUG_AGENT_AVAILABLE => [
            'name'          => "Agent available",
            'slug'          => Setting::SLUG_AGENT_AVAILABLE,
            'group'         => Setting::GROUP_AGENT,
            'applicable_to' => User::MORPH_ALIAS,
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
