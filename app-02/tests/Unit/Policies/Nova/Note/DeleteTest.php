<?php

namespace Tests\Unit\Policies\Nova\Note;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Policies\Nova\NotePolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_allows_to_delete_a_note()
    {
        $policy       = new NotePolicy();
        $user         = Mockery::mock(User::class);
        $note         = Mockery::mock(Note::class);
        $noteCategory = Mockery::mock(NoteCategory::class);
        $note->shouldReceive('getAttribute')->with('noteCategory')->andReturn($noteCategory);
        $note->noteCategory->shouldReceive('getRouteKey')->withNoArgs()->andReturn(NoteCategory::SLUG_FEATURED);
        $this->assertTrue($policy->delete($user, $note));
    }
}
