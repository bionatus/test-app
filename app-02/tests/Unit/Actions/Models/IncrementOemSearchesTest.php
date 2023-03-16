<?php

namespace Tests\Unit\Actions\Models;

use App\Actions\Models\IncrementOemSearches;
use App\Models\OemSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementOemSearchesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_an_oem_search_log_for_a_staff()
    {
        $staff    = Staff::factory()->createQuietly();
        $criteria = 'a search criteria';
        $results  = 1;

        $action = new IncrementOemSearches($staff, $criteria, $results);
        $action->execute();

        $this->assertDatabaseCount(OemSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(OemSearchCounter::tableName(), [
            'staff_id' => $staff->getKey(),
            'criteria' => $criteria,
            'results'  => $results,
        ]);
    }

    /** @test */
    public function it_stores_an_oem_search_log_for_a_user()
    {
        $user    = User::factory()->create();
        $criteria = 'a search criteria';
        $results  = 1;

        $action = new IncrementOemSearches($user, $criteria, $results);
        $action->execute();

        $this->assertDatabaseCount(OemSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(OemSearchCounter::tableName(), [
            'user_id'  => $user->getKey(),
            'criteria' => $criteria,
            'results'  => $results,
        ]);
    }

    /** @test */
    public function it_returns_an_oem_search_counter()
    {
        $staff    = Staff::factory()->createQuietly();
        $user     = User::factory()->create();
        $criteria = 'a search criteria';
        $results  = 1;

        $staffAction = new IncrementOemSearches($staff, $criteria, $results);
        $staffResult = $staffAction->execute();
        $this->assertInstanceOf(OemSearchCounter::class, $staffResult);

        $userAction = new IncrementOemSearches($user, $criteria, $results);
        $userResult = $userAction->execute();
        $this->assertInstanceOf(OemSearchCounter::class, $userResult);
    }
}
