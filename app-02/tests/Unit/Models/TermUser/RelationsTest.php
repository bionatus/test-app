<?php

namespace Tests\Unit\Models\TermUser;

use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property TermUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = TermUser::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_term()
    {
        $related = $this->instance->term()->first();

        $this->assertInstanceOf(Term::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
