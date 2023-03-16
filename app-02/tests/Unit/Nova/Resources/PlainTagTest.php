<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\PlainTag;
use App\Nova\Resources;
use Illuminate\Http\Request;
use Mockery;

class PlainTagTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(PlainTag::class, Resources\PlainTag::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\PlainTag::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'name',
        ], Resources\PlainTag::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\PlainTag::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\PlainTag::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\PlainTag::class, [
            'id',
            'name',
            'images',
        ]);
    }

    /** @test */
    public function it_does_not_authorize_the_resource_deletion()
    {
        $plainTag         = Mockery::mock(PlainTag::class);
        $plainTagResource = new Resources\PlainTag($plainTag);

        $this->assertSame(false, $plainTagResource->authorizedToDelete(new Request()));
    }
}
