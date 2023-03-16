<?php

namespace Tests\Unit\Models;

use App\Models\ControlBoard;
use App\Models\IsPart;
use ReflectionException;

class ControlBoardTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(ControlBoard::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ControlBoard::tableName(), [
            'id',
            'fused',
        ]);
    }
}
