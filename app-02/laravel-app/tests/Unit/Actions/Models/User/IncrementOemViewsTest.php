<?php

namespace Tests\Unit\Actions\Models\User;

use App\Actions\Models\User\IncrementOemViews;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementOemViewsTest extends TestCase
{
    use RefreshDatabase;

    private User             $user;
    private Oem              $oem;
    private OemSearchCounter $oemSearchCounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oem              = Oem::factory()->create();
        $this->user             = User::factory()->create();
        $this->oemSearchCounter = OemSearchCounter::factory()->create();
    }

    /** @test */
    public function it_increments_the_oem_views_counter()
    {
        (new IncrementOemViews($this->user, $this->oem, $this->oemSearchCounter))->execute();

        $this->assertDatabaseCount(OemDetailCounter::tableName(), 1);
        $this->assertDatabaseHas(OemDetailCounter::tableName(), [
            'oem_id'                => $this->oem->getKey(),
            'user_id'               => $this->user->getKey(),
            'oem_search_counter_id' => $this->oemSearchCounter->getKey(),
        ]);
    }
}
