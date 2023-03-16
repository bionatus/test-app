<?php

namespace Tests\Unit\Models\SeriesUser;

use App\Models\Series;
use App\Models\SeriesUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SeriesUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SeriesUser::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_series()
    {
        $related = $this->instance->series()->first();

        $this->assertInstanceOf(Series::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
