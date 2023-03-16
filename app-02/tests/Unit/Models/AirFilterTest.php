<?php

namespace Tests\Unit\Models;

use App\Models\AirFilter;
use App\Models\IsPart;
use ReflectionException;

class AirFilterTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(AirFilter::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(AirFilter::tableName(), [
            'id',
            'media_type',
            'merv_rating',
            'nominal_width',
            'nominal_length',
            'nominal_depth',
            'actual_width',
            'actual_length',
            'actual_depth',
            'efficiency',
            'max_operating_temp',
        ]);
    }
}
