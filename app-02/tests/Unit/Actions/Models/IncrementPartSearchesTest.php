<?php

namespace Tests\Unit\Actions\Models;

use App\Actions\Models\IncrementPartSearches;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementPartSearchesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_a_part_search_log_for_a_staff()
    {
        $staff    = Staff::factory()->createQuietly();
        $criteria = 'a search criteria';
        $results  = 1;

        $action = new IncrementPartSearches($staff, $criteria, $results);
        $action->execute();

        $this->assertDatabaseCount(PartSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(PartSearchCounter::tableName(), [
            'staff_id' => $staff->getKey(),
            'criteria' => $criteria,
            'results'  => $results,
        ]);
    }

    /** @test */
    public function it_stores_a_part_search_log_for_a_user()
    {
        $user    = User::factory()->create();
        $criteria = 'a search criteria';
        $results  = 1;

        $action = new IncrementPartSearches($user, $criteria, $results);
        $action->execute();

        $this->assertDatabaseCount(PartSearchCounter::tableName(), 1);
        $this->assertDatabaseHas(PartSearchCounter::tableName(), [
            'user_id' => $user->getKey(),
            'criteria' => $criteria,
            'results'  => $results,
        ]);
    }

    /** @test */
    public function it_returns_a_part_search_counter()
    {
        $staff    = Staff::factory()->createQuietly();
        $user    = User::factory()->create();
        $criteria = 'a search criteria';
        $results  = 1;

        $staffAction = new IncrementPartSearches($staff, $criteria, $results);
        $staffResult = $staffAction->execute();
        $this->assertInstanceOf(PartSearchCounter::class, $staffResult);

        $userAction = new IncrementPartSearches($user, $criteria, $results);
        $userResult = $userAction->execute();
        $this->assertInstanceOf(PartSearchCounter::class, $userResult);
    }
}
