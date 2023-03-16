<?php

namespace Tests\Unit\Models;

use App\Models\FanBlade;
use App\Models\IsPart;
use ReflectionException;

class FanBladeTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(FanBlade::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(FanBlade::tableName(), [
            'id',
            'diameter',
            'number_of_blades',
            'pitch',
            'bore',
            'rotation',
            'rpm',
            'cfm',
            'bhp',
            'material',
        ]);
    }
}
