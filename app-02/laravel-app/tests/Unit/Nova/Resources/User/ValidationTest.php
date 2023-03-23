<?php

namespace Tests\Unit\Nova\Resources\User;

use App\Constants\RequestKeys;
use App\Nova\Resources;
use Illuminate\Support\Collection;
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
        $this->resource = $this->novaResource(Resources\User::class);
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_first_name_field_is_required()
    {
        $field = $this->resource->field('first_name');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_first_name_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('first_name');
        $field->assertHasRule('max:255');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_last_name_field_is_required()
    {
        $field = $this->resource->field('last_name');
        $field->assertHasRule('required');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_last_name_field_must_be_under_255_chars()
    {
        $field = $this->resource->field('last_name');
        $field->assertHasRule('max:255');
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
        $field->assertHasCreationRule('unique:users,email');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_email_field_must_be_unique_on_update()
    {
        $field = $this->resource->field('email');
        $field->assertHasUpdateRule('unique:users,email,{{resourceId}}');
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
    public function its_password_field_must_have_at_least_6_chars_on_creation()
    {
        $field = $this->resource->field('password');
        $field->assertHasCreationRule('min:6');
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
    public function its_password_field_must_have_at_least_6_chars_on_update()
    {
        $field = $this->resource->field('password');
        $field->assertHasUpdateRule('min:6');
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_name_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_NAME);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_NAME)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_type_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_TYPE);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_TYPE)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_job_title_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::JOB_TITLE);
        $field->assertHasRule('required_with:' . implode(',', $this->companyFields(RequestKeys::JOB_TITLE)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_country_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_COUNTRY);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_COUNTRY)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_state_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_STATE);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_STATE)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_zip_code_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_ZIP_CODE);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_ZIP_CODE)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_address_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_ADDRESS);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_ADDRESS)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_company_city_must_be_required_with_other_company_fields()
    {
        $field = $this->resource->field(RequestKeys::COMPANY_CITY);
        $field->assertHasRule('required_with:' . implode(',',
                $this->companyFields(RequestKeys::COMPANY_CITY)->toArray()));
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_phone_number_must_be_required_with_country_code_field()
    {
        $field = $this->resource->field(RequestKeys::PHONE);
        $field->assertHasRule('required_with:' . RequestKeys::COUNTRY_CODE);
    }

    /** @test
     * @throws FieldNotFoundException
     */
    public function its_country_code_must_be_required_with_phone_field()
    {
        $field = $this->resource->field(RequestKeys::COUNTRY_CODE);
        $field->assertHasRule('required_with:' . RequestKeys::PHONE);
    }

    private function companyFields(string $except = ''): Collection
    {
        return Collection::make([
            RequestKeys::COMPANY_NAME,
            RequestKeys::COMPANY_TYPE,
            RequestKeys::COMPANY_COUNTRY,
            RequestKeys::COMPANY_STATE,
            RequestKeys::COMPANY_CITY,
            RequestKeys::COMPANY_ZIP_CODE,
            RequestKeys::COMPANY_ADDRESS,
            RequestKeys::JOB_TITLE,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE,
        ])->filter(fn($value) => $value != $except);
    }
}
