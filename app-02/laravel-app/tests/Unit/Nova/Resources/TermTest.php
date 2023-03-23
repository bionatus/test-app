<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Term;
use App\Nova\Resources;

class TermTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Term::class, Resources\Term::$model);
    }

    /** @test */
    public function it_uses_the_title_as_title()
    {
        $this->assertSame('title', Resources\Term::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'title',
            'link',
            'required_at',
        ], Resources\Term::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\Term::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\Term::group());
    }

    /** @test */
    public function it_as_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\Term::class, [
            'id',
            'title',
            'body',
            'link',
            'required_at',
        ]);
    }
}
