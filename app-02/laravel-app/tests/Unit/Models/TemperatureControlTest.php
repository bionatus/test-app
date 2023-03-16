<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\TemperatureControl;
use ReflectionException;

class TemperatureControlTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(TemperatureControl::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(TemperatureControl::tableName(), [
            'id',
            'programmable',
            'application',
            'wifi',
            'power_requirements',
            'operating_voltage',
            'switch',
            'action',
            'operation_of_contacts',
            'adjustable',
            'range_minimum',
            'range_maximum',
            'reset_minimum',
            'reset_maximum',
            'differential_minimum',
            'differential_maximum',
            'setpoint',
            'reset',
            'reset_type',
            'capillary_length',
            'max_amps',
            'max_volts',
            'replaceable_bulb',
            'mount',
        ]);
    }
}
