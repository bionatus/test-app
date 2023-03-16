<?php

namespace Tests\Unit\Actions\Models\Staff;

use App\Actions\Models\Staff\IncrementPartViews;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementPartViewsTest extends TestCase
{
    use RefreshDatabase;

    private Staff             $staff;
    private Part              $part;
    private PartSearchCounter $partSearchCounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->part              = Part::factory()->create();
        $this->staff             = Staff::factory()->createQuietly();
        $this->partSearchCounter = PartSearchCounter::factory()->create();
    }

    /** @test */
    public function it_increments_the_part_views_counter()
    {
        (new IncrementPartViews($this->staff, $this->part, $this->partSearchCounter))->execute();

        $this->assertDatabaseCount(PartDetailCounter::tableName(), 1);
        $this->assertDatabaseHas(PartDetailCounter::tableName(), [
            'part_id'                => $this->part->getKey(),
            'staff_id'               => $this->staff->getKey(),
            'part_search_counter_id' => $this->partSearchCounter->getKey(),
        ]);
    }
}
