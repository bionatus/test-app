<?php

namespace Tests\Unit\Nova\Resources\User;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
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
        $this->resource = $this->novaResource(Resources\User::class);
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
            ['first_name', true, true, true, true],
            ['last_name', true, true, true, true],
            ['email', true, true, true, true],
            ['password', false, true, false, true],
            ['created_at', true, false, true, false],
            [MediaCollectionNames::IMAGES, true, true, true, true],
            ['address', false, true, true, true],
            ['address_2', false, true, true, true],
            ['country', false, true, true, true],
            ['state', false, true, false, true],
            ['zip', false, true, true, true],
            ['city', false, true, true, true],
            [RequestKeys::COMPANY_NAME, false, true, true, true],
            [RequestKeys::COMPANY_TYPE, false, true, true, true],
            [RequestKeys::JOB_TITLE, false, true, true, true],
            ['display_' . RequestKeys::JOB_TITLE, false, false, true, false],
            [RequestKeys::COMPANY_COUNTRY, false, true, true, true],
            [RequestKeys::COMPANY_STATE, false, true, false, true],
            [RequestKeys::COMPANY_ZIP_CODE, false, true, true, true],
            [RequestKeys::COMPANY_CITY, false, true, true, true],
            [RequestKeys::COUNTRY_CODE, false, true, true, true],
            [RequestKeys::PHONE, true, true, true, true],
            ['hubspot_form', false, false, true, false],
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
    public function its_first_name_field_is_sortable()
    {
        $field = $this->resource->field('first_name');
        $field->assertSortable();
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_last_name_field_is_sortable()
    {
        $field = $this->resource->field('last_name');
        $field->assertSortable();
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_is_sortable()
    {
        $field = $this->resource->field('email');
        $field->assertSortable();
    }
}
