<?php

namespace Tests\Unit\Nova\Resources\AppVersion;

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
        $this->resource = $this->novaResource(Resources\AppVersion::class);
    }

    /** @test
     *
     * @dataProvider dataProvider
     * @throws FieldNotFoundException
     */
    public function it_shows_fields_when_correspond(
        string $fieldName,
        bool $shownOnIndex,
        bool $shownWhenCreating,
        bool $shownOnDetail,
        bool $shownWhenUpdating
    ) {
        $field = $this->resource->field($fieldName);
        if ($shownOnIndex) {
            $field->assertShownOnIndex("$fieldName is not shown on index");
        } else {
            $field->assertHiddenFromIndex("$fieldName is not hidden on index");
        }
        if ($shownWhenCreating) {
            $field->assertShownWhenCreating("$fieldName is not shown when creating");
        } else {
            $field->assertHiddenWhenCreating("$fieldName is not hidden when creating");
        }
        if ($shownOnDetail) {
            $field->assertShownOnDetail("$fieldName is not shown on detail");
        } else {
            $field->assertHiddenFromDetail("$fieldName is not hidden on detail");
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
            ['min', true, true, true, true],
            ['current', true, true, true, true],
            ['video_title', true, true, true, true],
            ['video_url', true, true, true, true],
            ['message', false, true, true, true],
        ];
    }
}
