<?php

namespace Tests\Unit\Database\Seeders;

use App\Models\AppVersion;
use Database\Seeders\AppVersionsSeeder;
use Database\Seeders\EnvironmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class AppVersionsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(AppVersionsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_a_version()
    {
        (new AppVersionsSeeder())->run();

        $this->assertDatabaseHas(AppVersion::tableName(), [
            'min'     => '0.0.0',
            'current' => '7.0.0',
            'message' => 'foo: bar',
        ]);
    }

    /** @test */
    public function it_does_not_stores_a_version_if_a_record_already_exist()
    {
        AppVersion::factory()->create($values = [
            'min'     => '0.0.0',
            'current' => '7.0.0',
            'message' => 'foo: bar',
        ]);

        (new AppVersionsSeeder())->run();

        $this->assertDatabaseHas(AppVersion::tableName(), $values);
        $this->assertDatabaseCount(AppVersion::class, 1);
    }
}
