<?php

namespace Tests\Unit\Nova\Resources\AppSetting;

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
        $this->resource = $this->novaResource(Resources\AppSetting::class);
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_value_field_must_be_required()
    {
        $field = $this->resource->field('value');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_value_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('value');
        $field->assertHasRule('max:255');
    }
}
