<?php

namespace Tests\Unit\Models;

use App\Models\Igniter;
use App\Models\IsPart;
use ReflectionException;

class IgniterTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Igniter::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Igniter::tableName(), [
            'id',
            'application',
            'gas_type',
            'voltage',
            'terminal_type',
            'mounting',
            'tip_style',
            'ceramic_block',
            'pilot_btu',
            'orifice_diameter',
            'pilot_tube_length',
            'lead_length',
            'sensor_type',
            'steady_current',
            'temp_rating',
            'time_to_temp',
            'amperage',
            'cold_resistance',
            'max_current',
            'compression_fitting_diameter',
            'probe_length',
            'rod_angle',
            'length',
            'height',
            'width',
        ]);
    }
}
