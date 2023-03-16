<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\RegistrationCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationCompletedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_users_by_registration_completed()
    {
        $registrationCompletedCount = 20;
        User::factory()->count(10)->create();
        User::factory()->count($registrationCompletedCount)->create(['registration_completed' => true]);

        $filteredUsers = User::scoped(new RegistrationCompleted())->get();

        $this->assertCount($registrationCompletedCount, $filteredUsers);
    }
}
