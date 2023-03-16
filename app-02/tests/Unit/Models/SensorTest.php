<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\Sensor;
use ReflectionException;

class SensorTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Sensor::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Sensor::tableName(), [
            'id',
            'application',
            'signal_type',
            'measurement_range',
            'connection_type',
            'configuration',
            'number_of_wires',
            'accuracy',
            'enclosure_rating',
            'lead_length',
            'operating_temperature',
        ]);
    }
}
