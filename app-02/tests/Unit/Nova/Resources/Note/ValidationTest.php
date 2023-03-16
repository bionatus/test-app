<?php

namespace Tests\Unit\Nova\Resources\Note;

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
        $this->resource = $this->novaResource(Resources\Note::class);
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
    public function its_body_field_must_be_under_91_chars()
    {
        $field = $this->resource->field('body');
        $field->assertHasRule('max:90');
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
    public function its_link_text_field_must_be_under_256_chars()
    {
        $field = $this->resource->field('link_text');
        $field->assertHasRule('max:255');
    }
}
