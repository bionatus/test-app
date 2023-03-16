<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\AddInitialTermUsersCommand;
use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddInitialTermUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_initial_relation_term_users_for_users_with_field_term_filled()
    {
        $currentTerm     = Term::factory()->create();
        $userWithTerm    = User::factory()->createQuietly(['terms' => true]);
        $userWithoutTerm = User::factory()->createQuietly(['terms' => false]);

        $command = new AddInitialTermUsersCommand();
        $command->handle();

        $this->assertDatabaseHas(TermUser::tableName(), [
            'user_id' => $userWithTerm->getKey(),
            'term_id' => $currentTerm->getKey(),
        ]);

        $this->assertDatabaseMissing(TermUser::tableName(), [
            'user_id' => $userWithoutTerm->getKey(),
            'term_id' => $userWithoutTerm->getKey(),
        ]);
    }
}
