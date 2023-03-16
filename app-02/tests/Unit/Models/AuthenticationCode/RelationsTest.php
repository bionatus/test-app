<?php

namespace Tests\Unit\Models\AuthenticationCode;

use App\Models\AuthenticationCode;
use App\Models\Phone;
use Tests\Unit\Models\RelationsTestCase;

class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = AuthenticationCode::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_phone()
    {
        $related = $this->instance->phone()->first();

        $this->assertInstanceOf(Phone::class, $related);
    }
}
