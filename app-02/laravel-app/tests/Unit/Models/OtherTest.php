<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\Other;
use ReflectionException;

class OtherTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Other::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Other::tableName(), [
            'id',
            'sort',
            'description',
        ]);
    }
}
