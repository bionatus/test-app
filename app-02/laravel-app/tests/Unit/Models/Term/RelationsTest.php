<?php

namespace Tests\Unit\Models\Term;

use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Term $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Term::factory()->create();
    }

    /** @test */
    public function it_has_users()
    {
        TermUser::factory()->usingTerm($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_term_users()
    {
        TermUser::factory()->usingTerm($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->termUsers()->get();

        $this->assertCorrectRelation($related, TermUser::class);
    }
}
