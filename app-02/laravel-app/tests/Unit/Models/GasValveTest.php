<?php

namespace Tests\Unit\Models;

use App\Models\GasValve;
use App\Models\IsPart;
use ReflectionException;

class GasValveTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(GasValve::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(GasValve::tableName(), [
            'id',
            'type_of_gas',
            'stages',
            'capacity',
            'outlet_orientation',
            'reducer_bushing',
            'inlet_size',
            'outlet_size_type',
            'pilot_outlet_size',
            'factory_settings',
            'max_inlet_pressure',
            'min_adjustable_setting',
            'max_adjustable_setting',
            'terminal_type',
            'electrical_rating',
            'side_outlet_size_type',
            'gas_cock_dial_markings',
            'ambient_temperature',
            'amp_rating',
            'capillary_length',
            'standard_dial',
            'remote_dial',
            'temperature_range',
            'height',
            'length',
            'width',
            'opening_speed',
        ]);
    }
}
