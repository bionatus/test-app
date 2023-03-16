<?php

namespace Tests\Unit\Nova\Resources\Term;

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
        $this->resource = $this->novaResource(Resources\Term::class);
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_title_field_is_required()
    {
        $field = $this->resource->field('title');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_title_field_must_be_under_27_chars()
    {
        $field = $this->resource->field('title');
        $field->assertHasRule('max:26');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_body_field_is_required()
    {
        $field = $this->resource->field('body');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_link_field_is_required()
    {
        $field = $this->resource->field('link');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_link_field_must_be_an_url()
    {
        $field = $this->resource->field('link');
        $field->assertHasRule('url');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_link_field_must_be_under_256_chars()
    {
        $field = $this->resource->field('link');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_required_at_field_is_required()
    {
        $field = $this->resource->field('required_at');
        $field->assertHasRule('required');
    }
}
