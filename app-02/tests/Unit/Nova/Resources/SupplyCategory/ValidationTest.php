<?php

namespace Tests\Unit\Nova\Resources\SupplyCategory;

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
        $this->resource = $this->novaResource(Resources\SupplyCategory::class);
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
    public function its_sort_field_is_nullable()
    {
        $field = $this->resource->field('sort');
        $field->assertHasRule('nullable');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_sort_field_must_be_integer()
    {
        $field = $this->resource->field('sort');
        $field->assertHasRule('integer');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_sort_field_must_positive()
    {
        $field = $this->resource->field('sort');
        $field->assertHasRule('min:1');
    }
}
