<?php

namespace Tests\Unit\Policies\Nova\Note;

use App\Models\Note;
use App\Policies\Nova\NotePolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function it_allows_to_update_a_note()
    {
        $policy = new NotePolicy();
        $user   = Mockery::mock(User::class);
        $note   = Mockery::mock(Note::class);

        $this->assertTrue($policy->update($user, $note));
    }
}
