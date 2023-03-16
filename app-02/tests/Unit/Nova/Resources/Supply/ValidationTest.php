<?php

namespace Tests\Unit\Nova\Resources\Supply;

use App\Nova\Resources;
use JoshGaber\NovaUnit\Fields\FieldNotFoundException;
use JoshGaber\NovaUnit\Resources\InvalidNovaResourceException;
use JoshGaber\NovaUnit\Resources\MockResource;
use Tests\Unit\Nova\Resources\ResourceTestCase;

class ValidationTest extends ResourceTestCase
{
    private MockResource $resource;

    /**
     * @throws InvalidNovaResourceException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = $this->novaResource(Resources\Supply::class);
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_name_field_is_required()
    {
        $field = $this->resource->field('name');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_name_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('name');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_internnal_name_field_is_required()
    {
        $field = $this->resource->field('internal_name');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_internal_name_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('internal_name');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_supply_category_field_is_required()
    {
        $field = $this->resource->field('supplyCategory');
        $field->assertHasRule('required');
    }
}
