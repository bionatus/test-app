<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const LEVELS = [
        Level::SLUG_LEVEL_0 => [
            'name'        => 'Level 0',
            'slug'        => Level::SLUG_LEVEL_0,
            'from'        => 0,
            'to'          => 999,
            'coefficient' => 0.5,
        ],
        Level::SLUG_LEVEL_1 => [
            'name'        => 'Level 1',
            'slug'        => Level::SLUG_LEVEL_1,
            'from'        => 1000,
            'to'          => null,
            'coefficient' => 0.5,
        ],
    ];

    public function run()
    {
        foreach (self::LEVELS as $slug => $levelData) {
            Level::updateOrCreate(['slug' => $slug], $levelData);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
