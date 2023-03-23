<?php

namespace Tests\Unit\Actions\Models\Staff;

use App\Actions\Models\Staff\IncrementOemViews;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementOemViewsTest extends TestCase
{
    use RefreshDatabase;

    private Staff            $staff;
    private Oem              $oem;
    private OemSearchCounter $oemSearchCounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oem              = Oem::factory()->create();
        $this->staff            = Staff::factory()->createQuietly();
        $this->oemSearchCounter = OemSearchCounter::factory()->create();
    }

    /** @test */
    public function it_increments_the_oem_views_counter()
    {
        (new IncrementOemViews($this->staff, $this->oem, $this->oemSearchCounter))->execute();

        $this->assertDatabaseCount(OemDetailCounter::tableName(), 1);
        $this->assertDatabaseHas(OemDetailCounter::tableName(), [
            'oem_id'                => $this->oem->getKey(),
            'staff_id'              => $this->staff->getKey(),
            'oem_search_counter_id' => $this->oemSearchCounter->getKey(),
        ]);
    }
}
