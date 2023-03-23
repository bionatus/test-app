<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\PressureControl;
use ReflectionException;

class PressureControlTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(PressureControl::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PressureControl::tableName(), [
            'id',
            'setpoint',
            'reset',
            'range_minimum',
            'range_maximum',
            'reset_minimum',
            'reset_maximum',
            'differential_minimum',
            'differential_maximum',
            'operation_of_contacts',
            'switch',
            'action',
            'reset_type',
            'connection_type',
            'max_amps',
            'max_volts',
            'pole_form',
            'fla',
            'rated_hz',
            'rated_voltage',
            'lra',
            'temperature_rating',
            'tolerance',
        ]);
    }
}
