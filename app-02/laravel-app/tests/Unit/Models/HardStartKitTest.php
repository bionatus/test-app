<?php

namespace Tests\Unit\Models;

use App\Models\HardStartKit;
use App\Models\IsPart;
use ReflectionException;

class HardStartKitTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(HardStartKit::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(HardStartKit::tableName(), [
            'id',
            'operating_voltage',
            'max_hp',
            'min_hp',
            'max_tons',
            'min_tons',
            'max_capacitance',
            'min_capacitance',
            'tolerance',
            'torque_increase',
            'capacitor_size',
            'capacitor_voltage',
            'hard_start_type',
        ]);
    }
}
