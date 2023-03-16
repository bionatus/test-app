<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const VERSION = [
        'min'     => '0.0.0',
        'current' => '7.0.0',
        'message' => 'foo: bar',
    ];

    public function run()
    {
        $appVersion = AppVersion::first();
        $appVersion ? $appVersion->update(self::VERSION) : AppVersion::create(self::VERSION);
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
