<?php

namespace Tests\Unit\Nova\Resources\SupplyCategory;

use App\Constants\MediaCollectionNames;
use App\Nova\Resources;
use JoshGaber\NovaUnit\Fields\FieldNotFoundException;
use JoshGaber\NovaUnit\Resources\InvalidNovaResourceException;
use JoshGaber\NovaUnit\Resources\MockResource;
use Tests\CanRefreshDatabase;
use Tests\Unit\Nova\Resources\ResourceTestCase;

class DisplayTest extends ResourceTestCase
{
    use CanRefreshDatabase;

    private MockResource $resource;

    /**
     * @throws InvalidNovaResourceException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = $this->novaResource(Resources\SupplyCategory::class);
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
        $this->refreshDatabaseForSingleTest();

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
            ['id', true, true, true, true],
            ['name', true, true, true, true],
            ['sort', true, true, true, true],
            ['parent', true, true, true, true],
            ['children count', true, true, true, true],
            ['supplies count', true, true, true, true],
            [MediaCollectionNames::IMAGES, true, true, true, true],
            ['supplies', true, true, true, true],
            ['children', true, true, true, true],
        ];
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_id_field_is_sortable()
    {
        $field = $this->resource->field('id');
        $field->assertSortable();
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_name_field_is_sortable()
    {
        $field = $this->resource->field('name');
        $field->assertSortable();
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_sort_field_is_sortable()
    {
        $field = $this->resource->field('sort');
        $field->assertSortable();
    }
}
