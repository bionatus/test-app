<?php

namespace Tests\Unit\Models;

use App\Models\HasState;
use Tests\TestCase;

class HasStateTest extends TestCase
{
    const COUNTRY_CODE = 'US';
    const STATE_CODE   = 'FL';

    /** @test */
    public function it_returns_the_state_short_code()
    {
        $stateCode = self::COUNTRY_CODE . '-' . self::STATE_CODE;
        $myClass   = $this->classStub($stateCode);

        $this->assertSame(self::STATE_CODE, $myClass->getStateShortCode());
    }

    /** @test */
    public function it_returns_null_on_null_state()
    {
        $myClass = $this->classStub();

        $this->assertNull($myClass->getStateShortCode());
    }

    private function classStub($stateCode = null): object
    {
        return new class($stateCode) {
            use HasState;

            public $state;

            public function __construct($stateCode)
            {
                $this->state = $stateCode;
            }
        };
    }
}
