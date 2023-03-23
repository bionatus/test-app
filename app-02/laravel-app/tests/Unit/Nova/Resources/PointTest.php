<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Point;
use App\Nova\Resources;
use Illuminate\Http\Request;
use Mockery;

class PointTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Point::class, Resources\Point::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\Point::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'points_earned',
            'action',
            'created_at',
        ], Resources\Point::$search);
    }

    /** @test */
    public function it_should_not_be_displayed_in_navigation()
    {
        $this->assertFalse(Resources\Point::$displayInNavigation);
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\Point::class, [
            'id',
            'points_earned',
            'action',
            'created_at',
        ]);
    }

    /** @test */
    public function it_returns_a_resource_path_in_redirect_after_create()
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('all')->withAnyArgs()->twice()->andReturn([
            'viaResource'   => 'latam-users',
            'viaResourceId' => 1,
        ]);

        $point         = Mockery::mock(Point::class);
        $pointResource = new Resources\Point($point);

        $result = $pointResource::redirectAfterCreate($requestMock, $pointResource);

        $expected = '/resources/latam-users/1';

        $this->assertSame($expected, $result);
    }
}
