<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\SheaveAndPulley;
use ReflectionException;

class SheaveAndPulleyTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(SheaveAndPulley::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SheaveAndPulley::tableName(), [
            'id',
            'belt_type',
            'number_of_grooves',
            'bore_diameter',
            'outside_diameter',
            'adjustable',
            'bore_mate_type',
            'bushing_connection',
            'keyway_types',
            'keyway_height',
            'keyway_width',
            'minimum_dd',
            'maximum_dd',
            'material',
        ]);
    }
}
