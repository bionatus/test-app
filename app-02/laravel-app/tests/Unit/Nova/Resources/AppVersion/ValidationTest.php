<?php

namespace Tests\Unit\Nova\Resources\AppVersion;

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
        $this->resource = $this->novaResource(Resources\AppVersion::class);
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_min_field_is_required()
    {
        $field = $this->resource->field('min');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_min_field_must_be_a_version()
    {
        $field = $this->resource->field('min');
        $field->assertHasRule('regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_current_field_is_required()
    {
        $field = $this->resource->field('current');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_current_field_must_be_a_version()
    {
        $field = $this->resource->field('current');
        $field->assertHasRule('regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_message_field_is_required()
    {
        $field = $this->resource->field('message');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_video_url_field_is_nullable()
    {
        $field = $this->resource->field('video_url');
        $field->assertHasRule('nullable');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_video_url_field_must_be_an_url()
    {
        $field = $this->resource->field('video_url');
        $field->assertHasRule('url');
    }
}
