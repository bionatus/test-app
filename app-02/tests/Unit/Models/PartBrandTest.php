<?php

namespace Tests\Unit\Models;

use App\Models\PartBrand;

class PartBrandTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PartBrand::tableName(), [
            'id',
            'slug',
            'name',
            'logo',
            'preferred',
            'published_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_slug()
    {
        $partBrand = PartBrand::factory()->create(['slug' => 'something']);

        $this->assertEquals($partBrand->slug, $partBrand->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $partBrand = PartBrand::factory()->make(['slug' => null]);
        $partBrand->save();

        $this->assertNotNull($partBrand->slug);
    }
}
