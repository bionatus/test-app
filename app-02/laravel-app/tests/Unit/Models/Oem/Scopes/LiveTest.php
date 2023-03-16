<?php

namespace Tests\Unit\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\Oem\Scopes\Live;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_out_oems_whose_status_is_not_live()
    {
        Oem::factory()->pending()->count(2)->create();
        Oem::factory()->live()->count(3)->create();

        $filtered = Oem::scoped(new Live())->get();

        $this->assertCount(3, $filtered);
    }
}
