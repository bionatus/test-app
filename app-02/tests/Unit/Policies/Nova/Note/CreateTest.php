<?php

namespace Tests\Unit\Policies\Nova\Note;

use App\Models\Note;
use App\Policies\Nova\NotePolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /** @test */
    public function it_allows_to_create_a_note()
    {
        $policy = new NotePolicy();
        $user   = Mockery::mock(User::class);

        $this->assertTrue($policy->create($user));
    }
}
