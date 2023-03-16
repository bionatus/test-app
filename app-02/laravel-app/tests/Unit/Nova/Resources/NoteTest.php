<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Note;
use App\Nova\Resources;

class NoteTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Note::class, Resources\Note::$model);
    }

    /** @test */
    public function it_uses_the_title_as_title()
    {
        $this->assertSame('title', Resources\Note::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'slug',
            'title',
            'body',
            'link',
            'link_text',
        ], Resources\Note::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\Note::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\Note::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\Note::class, [
            'id',
            'images',
            'slug',
            'title',
            'body',
            'link',
            'link_text',
            'sort',
            'noteCategory',
        ]);
    }
}
