<?php

namespace Tests\Unit\Models;

use App\Models\Capacitor;
use App\Models\IsPart;
use ReflectionException;

class CapacitorTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Capacitor::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Capacitor::tableName(), [
            'id',
            'microfarads',
            'voltage',
            'shape',
            'tolerance',
            'operating_temperature',
            'depth',
            'height',
            'width',
            'part_number_correction',
            'notes',
        ]);
    }
}
