<?php

namespace Tests\Unit\Nova\Resources\Supplier;

use App\Constants\MediaCollectionNames;
use App\Models\Staff;
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
        $this->resource = $this->novaResource(Resources\Supplier::class);
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
            // fieldName, shownOnIndex, shownWhenCreating, shownOnDetail, shownOnUpdating
            ['id', true, true, true, true],
            ['airtable_id', false, false, true, false],
            ['name', true, true, true, true],
            [MediaCollectionNames::LOGO, true, true, true, true],
            [MediaCollectionNames::IMAGES, false, true, true, true],
            ['branch', false, true, true, true],
            ['address', true, true, true, true],
            ['address_2', false, true, true, true],
            ['country', false, true, true, true],
            ['state', false, true, true, true],
            ['display_state', false, false, true, false],
            ['city', true, true, true, true],
            ['zip_code', false, true, true, true],
            ['email', true, true, true, true],
            ['password', false, true, false, true],
            ['phone', false, true, true, true],
            ['prokeep_phone', false, true, true, true],
            ['offers_delivery', false, true, true, true],
            ['terms', false, true, true, true],
            ['about', false, true, true, true],
            ['contact_phone', false, true, true, true],
            ['contact_email', false, true, true, true],
            ['contact_secondary_email', false, true, true, true],
            [Staff::TYPE_MANAGER . '_name', false, true, true, true],
            [Staff::TYPE_MANAGER . '_phone', false, true, true, true],
            [Staff::TYPE_MANAGER . '_email', false, true, true, true],
            [Staff::TYPE_ACCOUNTANT . '_name', false, true, true, true],
            [Staff::TYPE_ACCOUNTANT . '_phone', false, true, true, true],
            [Staff::TYPE_ACCOUNTANT . '_email', false, true, true, true],
            ['location', false, true, true, true],
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
}
