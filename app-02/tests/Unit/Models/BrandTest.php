<?php

namespace Tests\Unit\Models;

use App\Models\Brand;

class BrandTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Brand::tableName(), [
            'id',
            'slug',
            'name',
            'logo',
            'published_at',
            'created_at',
            'updated_at',
        ]);
    }
}
