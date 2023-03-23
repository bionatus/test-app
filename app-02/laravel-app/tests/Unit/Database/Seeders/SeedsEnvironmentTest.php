<?php

namespace Tests\Unit\Database\Seeders;

use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\SeedsEnvironment;
use Illuminate\Database\Seeder;
use Tests\TestCase;

class SeedsEnvironmentTest extends TestCase
{
    /** @test */
    public function it_can_run_on_specific_environments()
    {
        $testingSeeder    = $this->seeder(['testing']);
        $productionSeeder = $this->seeder(['production']);
        $this->assertTrue($testingSeeder->canRunInEnvironment());
        $this->assertFalse($productionSeeder->canRunInEnvironment());
    }

    /** @test */
    public function it_runs_on_specific_environments()
    {
        $testingSeeder    = $this->seeder(['testing']);
        $productionSeeder = $this->seeder(['production']);
        $this->assertSame('has run', $testingSeeder->__invoke());
        $this->assertNull($productionSeeder->__invoke());
    }

    private function seeder(array $environments)
    {
        return new class($environments) extends Seeder implements EnvironmentSeeder {
            use SeedsEnvironment;

            private array $environments;

            public function __construct(array $environments)
            {
                $this->environments = $environments;
            }

            public function environments(): array
            {
                return $this->environments;
            }

            public function run()
            {
                return 'has run';
            }
        };
    }
}
