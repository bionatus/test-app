<?php

namespace Tests\Unit\Models;

use App\Models\FilterDrierAndCore;
use App\Models\IsPart;
use ReflectionException;

class FilterDrierAndCoreTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(FilterDrierAndCore::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(FilterDrierAndCore::tableName(), [
            'id',
            'volume',
            'inlet_diameter',
            'inlet_connection_type',
            'outlet_diameter',
            'outlet_connection_type',
            'direction_of_flow',
            'desiccant_type',
            'number_of_cores',
            'options',
            'rated_capacity',
            'note',
        ]);
    }
}
