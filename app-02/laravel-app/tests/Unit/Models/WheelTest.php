<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\Wheel;
use ReflectionException;

class WheelTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Wheel::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Wheel::tableName(), [
            'id',
            'diameter',
            'width',
            'bore',
            'rotation',
            'max_rpm',
            'material',
            'keyway',
            'center_disc',
            'number_hubs',
            'hub_lock',
            'number_setscrews',
            'number_blades',
            'wheel_type',
            'drive_type',
        ]);
    }
}
