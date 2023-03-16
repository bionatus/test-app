<?php

namespace Tests\Unit\Nova\Resources\SupplierCompany;

use App\Models\SupplierCompany;
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
        $this->resource = $this->novaResource(Resources\SupplierCompany::class);
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
    public function its_name_field_must_be_under_100_chars()
    {
        $field = $this->resource->field('name');
        $field->assertHasRule('max:100');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_can_be_null()
    {
        $field = $this->resource->field('email');
        $field->assertHasRule('nullable');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('email');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_be_a_valid_email()
    {
        $field = $this->resource->field('email');
        $field->assertHasRule('bail');
        $field->assertHasRule('email:strict');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_end_with_a_valid_top_level_domain()
    {
        $field = $this->resource->field('email');
        $field->assertHasRule('ends_with_tld');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_be_unique_on_creation()
    {
        $field = $this->resource->field('email');
        $field->assertHasCreationRule('unique:' . SupplierCompany::tableName() . ',email');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_be_unique_on_update()
    {
        $field = $this->resource->field('email');
        $field->assertHasUpdateRule('unique:' . SupplierCompany::tableName() . ',email,{{resourceId}}');
    }
}
