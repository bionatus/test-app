<?php

namespace Tests\Unit\Models;

use App\Models\Belt;
use App\Models\IsPart;
use ReflectionException;

class BeltTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Belt::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Belt::tableName(), [
            'id',
            'family',
            'belt_type',
            'belt_length',
            'pitch',
            'thickness',
            'top_width',
            'temperature_rating',
        ]);
    }
}
