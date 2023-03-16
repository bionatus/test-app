<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\Alphabetically;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AlphabeticallyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_first_name_and_last_name_alphabetically()
    {
        $users = Collection::make([]);
        $users->add(User::factory()->create(['first_name' => 'John B']));
        $users->add(User::factory()->create(['first_name' => 'John C', 'last_name' => 'Acme']));
        $users->prepend(User::factory()->create(['first_name' => 'John A', 'last_name' => 'Button']));
        $users->prepend(User::factory()->create(['first_name' => 'John A', 'last_name' => 'Acme']));

        $orderedUsers = User::scoped(new Alphabetically())->get();

        $orderedUsers->each(function(User $user) use ($users) {
            $this->assertSame($users->shift()->getKey(), $user->getKey());
        });
    }
}
