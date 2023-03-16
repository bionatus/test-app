<?php

namespace Tests\Unit\Database\Seeders;

use App\Models\Level;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\LevelsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class LevelsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(LevelsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_levels()
    {
        $seeder = new LevelsSeeder();
        $seeder->run();

        foreach (LevelsSeeder::LEVELS as $levelData) {
            $this->assertDatabaseHas(Level::tableName(), $levelData);
        }
    }
}
