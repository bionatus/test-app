<?php

namespace Tests\Unit\Nova\Resources\SupportCallCategory;

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
        $this->resource = $this->novaResource(Resources\SupportCallCategory::class);
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
    public function its_description_field_is_nullable()
    {
        $field = $this->resource->field('description');
        $field->assertHasRule('nullable');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_description_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('description');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_phone_field_is_required()
    {
        $field = $this->resource->field('phone');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_phone_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('phone');
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
