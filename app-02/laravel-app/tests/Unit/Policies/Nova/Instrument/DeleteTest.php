<?php

namespace Tests\Unit\Policies\Nova\Instrument;

use App\Models\Instrument;
use App\Policies\Nova\InstrumentPolicy;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allow_to_delete_an_instrument_that_has_support_call_categories()
    {
        $children = Mockery::mock(BelongsToMany::class);
        $children->shouldReceive('doesntExist')->withNoArgs()->once()->andReturnFalse();

        $instrument = Mockery::mock(Instrument::class);
        $instrument->shouldReceive('supportCallCategories')->withNoArgs()->once()->andReturn($children);

        $policy = new InstrumentPolicy();

        $this->assertFalse($policy->delete(new User(), $instrument));
    }

    /** @test */
    public function it_allows_to_delete_a_instrument_that_does_not_have_support_call_categories()
    {
        $children = Mockery::mock(BelongsToMany::class);
        $children->shouldReceive('doesntExist')->withNoArgs()->once()->andReturnTrue();

        $instrument = Mockery::mock(Instrument::class);
        $instrument->shouldReceive('supportCallCategories')->withNoArgs()->once()->andReturn($children);

        $policy = new InstrumentPolicy();

        $this->assertTrue($policy->delete(new User(), $instrument));
    }
}
