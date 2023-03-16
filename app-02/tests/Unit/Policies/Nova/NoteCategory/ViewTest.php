<?php

namespace Tests\Unit\Policies\Nova\NoteCategory;

use App\Models\NoteCategory;
use App\Policies\Nova\NoteCategoryPolicy;
use App\User;
use Mockery;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_allows_to_view_a_note_category()
    {
        $policy       = new NoteCategoryPolicy();
        $user         = Mockery::mock(User::class);
        $noteCategory = Mockery::mock(NoteCategory::class);

        $this->assertTrue($policy->view($user, $noteCategory));
    }
}
