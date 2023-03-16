<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use Database\Seeders\DemoSOPSeeder;
use Database\Seeders\DemoSuppliesSeeder;
use Database\Seeders\EnvironmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class DemoSOPSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(DemoSOPSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new DemoSuppliesSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
