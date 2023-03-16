<?php

namespace Tests\Unit\Actions\Models\User;

use App\Actions\Models\User\IncrementPartViews;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementPartViewsTest extends TestCase
{
    use RefreshDatabase;

    private User              $user;
    private Part              $part;
    private PartSearchCounter $partSearchCounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->part              = Part::factory()->create();
        $this->user              = User::factory()->create();
        $this->partSearchCounter = PartSearchCounter::factory()->create();
    }

    /** @test */
    public function it_increments_the_part_views_counter()
    {
        (new IncrementPartViews($this->user, $this->part, $this->partSearchCounter))->execute();

        $this->assertDatabaseCount(PartDetailCounter::tableName(), 1);
        $this->assertDatabaseHas(PartDetailCounter::tableName(), [
            'part_id'                => $this->part->getKey(),
            'user_id'                => $this->user->getKey(),
            'part_search_counter_id' => $this->partSearchCounter->getKey(),
        ]);
    }
}
