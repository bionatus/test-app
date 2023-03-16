<?php

namespace Tests\Unit\Models\Phone;

use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Phone $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Phone::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $phone = Phone::factory()->withUser()->create();

        $related = $phone->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_authentication_codes()
    {
        AuthenticationCode::factory()->usingPhone($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->authenticationCodes()->get();

        $this->assertCorrectRelation($related, AuthenticationCode::class);
    }
}
