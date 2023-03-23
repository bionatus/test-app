<?php

namespace Tests\Unit\Nova\Resources\SupplierHour;

use App\Nova\Resources;
use JoshGaber\NovaUnit\Fields\FieldNotFoundException;
use JoshGaber\NovaUnit\Resources\InvalidNovaResourceException;
use JoshGaber\NovaUnit\Resources\MockResource;
use Tests\Unit\Nova\Resources\ResourceTestCase;

class DisplayTest extends ResourceTestCase
{
    private MockResource $resource;

    /**
     * @throws InvalidNovaResourceException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = $this->novaResource(Resources\SupplierHour::class);
    }

    /** @test
     *
     * @dataProvider dataProvider
     * @throws FieldNotFoundException
     */
    public function it_shows_fields_when_correspond(
        string $fieldName,
        bool $shownWhenCreating,
        bool $shownWhenUpdating
    ) {
        $field = $this->resource->field($fieldName);
        if ($shownWhenCreating) {
            $field->assertShownWhenCreating("$fieldName is not shown when creating");
        } else {
            $field->assertHiddenWhenCreating("$fieldName is not hidden when creating");
        }

        if ($shownWhenUpdating) {
            $field->assertShownWhenUpdating("$fieldName is not shown when updating");
        } else {
            $field->assertHiddenWhenUpdating("$fieldName is not hidden when updating");
        }
    }

    public function dataProvider(): array
    {
        return [
            // fieldName, shownWhenCreating, shownWhenUpdating
            ['id', true, true],
            ['day', true, false],
            ['from', true, true],
            ['to', true, true],
        ];
    }
}
