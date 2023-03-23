<?php

namespace Tests\Unit\Models\Technician\Scopes;

use App\Models\Technician;
use App\Models\Technicians\Scopes\ByShowInApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByShowInAppTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_show_in_app()
    {
        Technician::factory()->createMany([
            ['show_in_app' => true],
            ['show_in_app' => true],
            ['show_in_app' => false],
            ['show_in_app' => true],
            ['show_in_app' => true],
            ['show_in_app' => false],
        ]);

        $technicians = Technician::scoped(new ByShowInApp(true))->get();

        $this->assertCount(4, $technicians);
        $technicians->each(function(Technician $technician) {
            $this->assertEquals(true, $technician->show_in_app);
        });
    }
}
