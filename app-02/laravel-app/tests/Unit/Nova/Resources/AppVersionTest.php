<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\AppVersion;
use App\Nova\Resources;

class AppVersionTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(AppVersion::class, Resources\AppVersion::$model);
    }

    /** @test */
    public function it_uses_the_version_as_title()
    {
        $this->assertSame('current', Resources\AppVersion::$title);
    }

    /** @test */
    public function it_uses_version_field_for_search()
    {
        $this->assertSame([
            'min',
            'current',
            'video_title',
            'video_url',
            'message',
        ], Resources\AppVersion::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\AppVersion::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\AppVersion::group());
    }

    /** @test */
    public function it_as_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\AppVersion::class, [
            'min',
            'current',
            'video_title',
            'video_url',
            'message',
        ]);
    }
}
