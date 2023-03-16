<?php

namespace Tests\Unit\Nova\Resources\Supplier;

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
        $this->resource = $this->novaResource(Resources\Supplier::class);
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
    public function its_branch_field_can_be_null()
    {
        $field = $this->resource->field('branch');
        $field->assertHasRule('nullable');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_branch_field_must_be_integer()
    {
        $field = $this->resource->field('branch');
        $field->assertHasRule('integer');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_branch_field_must_be_at_least_1()
    {
        $field = $this->resource->field('branch');
        $field->assertHasRule('min:1');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_branch_field_must_be_digits_between_one_and_eight()
    {
        $field = $this->resource->field('branch');
        $field->assertHasRule('digits_between:1,8');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_country_must_be_required_with_state_field()
    {
        $field = $this->resource->field('country');
        $field->assertHasRule('required_with:state');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_is_required()
    {
        $field = $this->resource->field('email');
        $field->assertHasRule('required');
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
        $field->assertHasCreationRule('unique:suppliers,email');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_be_unique_on_update()
    {
        $field = $this->resource->field('email');
        $field->assertHasUpdateRule('unique:suppliers,email,{{resourceId}}');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_password_field_is_required_on_creation()
    {
        $field = $this->resource->field('password');
        $field->assertHasCreationRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_password_field_must_be_a_string_on_creation()
    {
        $field = $this->resource->field('password');
        $field->assertHasCreationRule('string');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_password_field_must_have_at_least_8_chars_on_creation()
    {
        $field = $this->resource->field('password');
        $field->assertHasCreationRule('min:8');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_password_field_is_nullable_on_update()
    {
        $field = $this->resource->field('password');
        $field->assertHasUpdateRule('nullable');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_password_field_must_be_a_string_on_update()
    {
        $field = $this->resource->field('password');
        $field->assertHasUpdateRule('string');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_password_field_must_have_at_least_8_chars_on_update()
    {
        $field = $this->resource->field('password');
        $field->assertHasUpdateRule('min:8');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_contact_email_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('contact_email');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_contact_email_field_must_be_a_valid_contact_email()
    {
        $field = $this->resource->field('contact_email');
        $field->assertHasRule('bail');
        $field->assertHasRule('email:strict');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_contact_email_field_must_end_with_a_valid_top_level_domain()
    {
        $field = $this->resource->field('contact_email');
        $field->assertHasRule('ends_with_tld');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_manager_email_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('manager_email');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_manager_email_field_must_be_a_valid_manager_email()
    {
        $field = $this->resource->field('manager_email');
        $field->assertHasRule('bail');
        $field->assertHasRule('email:strict');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_manager_email_field_must_end_with_a_valid_top_level_domain()
    {
        $field = $this->resource->field('manager_email');
        $field->assertHasRule('ends_with_tld');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_accountant_email_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('accountant_email');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_accountant_email_field_must_be_a_valid_accountant_email()
    {
        $field = $this->resource->field('accountant_email');
        $field->assertHasRule('bail');
        $field->assertHasRule('email:strict');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_accountant_email_field_must_end_with_a_valid_top_level_domain()
    {
        $field = $this->resource->field('accountant_email');
        $field->assertHasRule('ends_with_tld');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_take_rate_field_is_required()
    {
        $field = $this->resource->field('take_rate');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_take_rate_until_field_is_required()
    {
        $field = $this->resource->field('take_rate_until');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_terms_field_is_required()
    {
        $field = $this->resource->field('terms');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_terms_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('terms');
        $field->assertHasRule('max:255');
    }
}
