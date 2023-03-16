<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\AppSetting;
use App\Nova\Resources;
use Illuminate\Http\Request;
use Mockery;

class AppSettingTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(AppSetting::class, Resources\AppSetting::$model);
    }

    /** @test */
    public function it_uses_the_label_as_title()
    {
        $this->assertSame('label', Resources\AppSetting::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'label',
            'value',
        ], Resources\AppSetting::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\AppSetting::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\AppSetting::group());
    }

    /** @test */
    public function it_as_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\AppSetting::class, [
            'id',
            'label',
            'value',
            'value_display',
            'type',
        ]);
    }

    /** @test */
    public function it_returns_false_on_delete_authorization()
    {
        $resource = new Resources\AppSetting(null);
        $request  = Mockery::mock(Request::class);
        $this->assertFalse($resource->authorizedToDelete($request));
    }

    /** @test */
    public function it_returns_false_on_create_authorization()
    {
        $request = Mockery::mock(Request::class);
        $this->assertFalse(Resources\AppSetting::authorizedToCreate($request));
    }
}
