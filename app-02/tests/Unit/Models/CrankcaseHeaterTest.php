<?php

namespace Tests\Unit\Models;

use App\Models\CrankcaseHeater;
use App\Models\IsPart;
use ReflectionException;

class CrankcaseHeaterTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(CrankcaseHeater::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CrankcaseHeater::tableName(), [
            'id',
            'watts_power',
            'voltage',
            'shape',
            'min_dimension',
            'max_dimension',
            'probe_length',
            'probe_diameter',
            'lead_length',
        ]);
    }
}
