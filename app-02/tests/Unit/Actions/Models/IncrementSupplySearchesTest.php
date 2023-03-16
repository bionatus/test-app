<?php

namespace Tests\Unit\Actions\Models;

use App\Actions\Models\IncrementSupplySearches;
use App\Models\SupplySearchCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncrementSupplySearchesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_a_part_search_log_for_a_user()
    {
        $user     = User::factory()->create();
        $criteria = 'a search criteria';
        $results  = 1;

        $action = new IncrementSupplySearches($user, $criteria, $results);
        $action->execute();

        $this->assertDatabaseCount(SupplySearchCounter::tableName(), 1);
        $this->assertDatabaseHas(SupplySearchCounter::tableName(), [
            'user_id'  => $user->getKey(),
            'criteria' => $criteria,
            'results'  => $results,
        ]);
    }

    /** @test */
    public function it_returns_a_part_search_counter()
    {
        $user     = User::factory()->create();
        $criteria = 'a search criteria';
        $results  = 1;

        $userAction = new IncrementSupplySearches($user, $criteria, $results);
        $userResult = $userAction->execute();
        $this->assertInstanceOf(SupplySearchCounter::class, $userResult);
    }
}
