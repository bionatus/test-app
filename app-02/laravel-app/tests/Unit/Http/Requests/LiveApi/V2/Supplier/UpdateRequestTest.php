<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Supplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V2\SupplierController;
use App\Http\Requests\LiveApi\V2\Supplier\UpdateRequest;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SupplierController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected string $requestClass = UpdateRequest::class;
    private Staff    $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $supplier    = Supplier::factory()->createQuietly(['email' => 'supplier@email.com']);
        $this->staff = Staff::factory()->usingSupplier($supplier)->create();

        Auth::shouldReceive('user')->once()->andReturn($this->staff);
    }

    /** @test */
    public function it_requires_a_name()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::NAME])]);
    }

    /** @test */
    public function its_name_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::NAME])]);
    }

    /** @test */
    public function its_name_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::NAME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_an_email()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::EMAIL;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages(['The email field does not end with a valid tld.']);
    }

    /** @test */
    public function its_email_must_be_unique()
    {
        Supplier::factory()->createQuietly(['email' => $email = 'supplier2@email.com']);

        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => $email]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.unique', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_be_unique_but_it_can_be_the_same_as_the_logged_staff_supplier()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => $this->staff->supplier->email]);

        $request->assertValidationErrorsMissing([RequestKeys::EMAIL]);
    }

    /** @test */
    public function its_branch_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [$requestKey = RequestKeys::BRANCH => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_branch_value_must_be_greater_or_equal_to_one()
    {
        $request = $this->formRequest($this->requestClass, [$requestKey = RequestKeys::BRANCH => 0]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'min' => 1]),
        ]);
    }

    /** @test */
    public function its_branch_value_must_have_at_most_eight_digits()
    {
        $request = $this->formRequest($this->requestClass, [$requestKey = RequestKeys::BRANCH => 123456789]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'min' => 1, 'max' => 8]),
        ]);
    }

    /** @test */
    public function its_branch_and_name_combination_must_be_unique()
    {
        Supplier::factory()->createQuietly(['name' => $name = 'same name', 'branch' => $branch = 123456]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME                 => $name,
            $requestKey = RequestKeys::BRANCH => $branch,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.unique', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_branch_must_be_unique_but_it_can_be_the_same_as_the_logged_staff_supplier()
    {
        $this->staff->supplier->branch = $branch = 123;
        Supplier::flushEventListeners();
        $this->staff->supplier->save();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME                 => $this->staff->supplier->name,
            $requestKey = RequestKeys::BRANCH => $branch,
        ]);

        $request->assertValidationErrorsMissing([$requestKey]);
    }

    /** @test */
    public function it_requires_a_phone()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_phone_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHONE => 'not integer']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_phone_must_be_at_least_seven_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHONE => 123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_phone_must_be_at_most_fifteen_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHONE => 1234567890123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_prokeep_phone_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PROKEEP_PHONE => 'not integer']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PROKEEP_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PROKEEP_PHONE);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_prokeep_phone_must_be_at_least_seven_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PROKEEP_PHONE => 123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PROKEEP_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PROKEEP_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_prokeep_phone_must_be_at_most_fifteen_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PROKEEP_PHONE => 1234567890123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PROKEEP_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PROKEEP_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function it_requires_an_address()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::ADDRESS])]);
    }

    /** @test */
    public function its_address_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::ADDRESS])]);
    }

    /** @test */
    public function its_address_must_be_at_most_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::ADDRESS, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_address_2_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_2_must_be_at_most_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS_2]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_country()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::COUNTRY])]);
    }

    /** @test */
    public function its_country_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::COUNTRY])]);
    }

    /** @test */
    public function its_country_should_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::COUNTRY])]);
    }

    /** @test */
    public function it_requires_a_timezone()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TIMEZONE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::TIMEZONE])]);
    }

    /** @test */
    public function its_timezone_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TIMEZONE => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TIMEZONE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::TIMEZONE])]);
    }

    /** @test */
    public function its_timezone_should_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TIMEZONE => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TIMEZONE]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::TIMEZONE])]);
    }

    /** @test */
    public function it_requires_a_state()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::STATE])]);
    }

    /** @test */
    public function its_state_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::STATE])]);
    }

    /** @test */
    public function its_state_should_be_in_its_country()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY => 'US',
            RequestKeys::STATE   => 'invalid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::STATE])]);
    }

    /** @test */
    public function it_requires_a_city()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::CITY])]);
    }

    /** @test */
    public function its_city_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::CITY])]);
    }

    /** @test */
    public function its_city_must_be_at_most_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::CITY, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_zip_code()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_should_be_a_5_digits_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => '123456']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => 5]),
        ]);
    }

    /** @test */
    public function it_requires_a_contact_phone()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey = RequestKeys::CONTACT_PHONE]);
        $attribute = $this->getDisplayableAttribute($requestKey);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_phone_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_PHONE => 'not integer']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_PHONE);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_phone_must_be_at_least_seven_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_PHONE => 123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_contact_phone_must_be_at_most_fifteen_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_PHONE => 1234567890123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function it_requires_a_contact_email()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_email_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::CONTACT_EMAIL;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_contact_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_EMAIL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_EMAIL]);
        $request->assertValidationMessages(['The contact email field does not end with a valid tld.']);
    }

    /** @test */
    public function its_contact_secondary_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CONTACT_SECONDARY_EMAIL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_SECONDARY_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_SECONDARY_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_secondary_email_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::CONTACT_SECONDARY_EMAIL;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_contact_secondary_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::CONTACT_SECONDARY_EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_SECONDARY_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CONTACT_SECONDARY_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_contact_secondary_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::CONTACT_SECONDARY_EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CONTACT_SECONDARY_EMAIL]);
        $request->assertValidationMessages(['The contact secondary email field does not end with a valid tld.']);
    }

    /** @test */
    public function it_requires_a_manager_name()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_name_must_be_a_string()
    {
        $requestKey = RequestKeys::MANAGER_NAME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $attribute = $this->getDisplayableAttribute($requestKey);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_name_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::MANAGER_NAME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_manager_email()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MANAGER_EMAIL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_email_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::MANAGER_EMAIL;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_manager_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MANAGER_EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MANAGER_EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_EMAIL]);
        $request->assertValidationMessages(['The manager email field does not end with a valid tld.']);
    }

    /** @test */
    public function it_requires_a_manager_phone()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_PHONE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_phone_must_be_an_integer()
    {
        $requestKey = RequestKeys::MANAGER_PHONE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 'not integer']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $attribute = $this->getDisplayableAttribute($requestKey);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_manager_phone_must_be_at_least_seven_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MANAGER_PHONE => '123456']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_manager_phone_must_be_at_most_fifteen_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MANAGER_PHONE => '1234567890123456']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MANAGER_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MANAGER_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function it_requires_an_accountant_name()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_NAME);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_accountant_name_must_be_a_string()
    {
        $requestKey = RequestKeys::ACCOUNTANT_NAME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $attribute = $this->getDisplayableAttribute($requestKey);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_accountant_name_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::ACCOUNTANT_NAME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_an_accountant_phone()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_accountant_phone_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ACCOUNTANT_PHONE => 'not integer']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_PHONE);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_accountant_phone_must_be_at_least_seven_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ACCOUNTANT_PHONE => 123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_accountant_phone_must_be_at_most_fifteen_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ACCOUNTANT_PHONE => 1234567890123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function it_requires_an_accountant_email()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_EMAIL);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_accountant_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ACCOUNTANT_EMAIL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_accountant_email_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::ACCOUNTANT_EMAIL;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute($requestKey), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_accountant_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ACCOUNTANT_EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ACCOUNTANT_EMAIL);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_accountant_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ACCOUNTANT_EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ACCOUNTANT_EMAIL]);
        $request->assertValidationMessages(['The accountant email field does not end with a valid tld.']);
    }

    /** @test */
    public function it_requires_counter_staff()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTER_STAFF]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTER_STAFF);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_counter_staff_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTER_STAFF => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTER_STAFF]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTER_STAFF);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_the_counter_staff_to_ten()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => array_fill(0, 11, 'counter')]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTER_STAFF]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTER_STAFF);
        $request->assertValidationMessages([
            Lang::get('validation.max.array', ['attribute' => $attribute, 'max' => 10]),
        ]);
    }

    /** @test */
    public function each_item_in_counter_staff_must_have_a_name()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTER_STAFF => [['just a string']]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.name';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function the_name_parameter_in_counter_staff_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [['name' => ['array value']]]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.name';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function the_name_parameter_in_counter_staff_must_be_at_most_255_characters_long()
    {
        $requestKey = RequestKeys::COUNTER_STAFF;
        $request    = $this->formRequest($this->requestClass, [$requestKey => [['name' => Str::random(256)]]]);

        $request->assertValidationFailed();
        $attribute = $requestKey . '.0.name';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function the_email_parameter_in_counter_staff_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [['email' => ['array value']]]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.email';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function the_email_parameter_in_counter_staff_must_be_at_most_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [['email' => Str::random(256)]]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.email';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function the_email_parameter_in_counter_staff_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [['email' => 'invalid @email.com']]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.email';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([
            Lang::get('validation.email', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function the_email_parameter_in_counter_staff_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [['email' => 'valid@valid.invalid']]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.email';
        $request->assertValidationErrors([$attribute]);
        $request->assertValidationMessages([
            Lang::get('The :attribute field does not end with a valid tld.', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function the_phone_parameter_on_counter_staff_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [[RequestKeys::PHONE => 'Not integer']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = RequestKeys::COUNTER_STAFF . '.0.' . RequestKeys::PHONE;
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function the_phone_parameter_on_counter_staff_must_be_at_least_seven_digits()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [[RequestKeys::PHONE => 123456]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = RequestKeys::COUNTER_STAFF . '.0.' . RequestKeys::PHONE;
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function the_phone_parameter_on_counter_staff_must_be_at_most_fifteen_digits()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [[RequestKeys::PHONE => 1234567890123456]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = RequestKeys::COUNTER_STAFF . '.0.' . RequestKeys::PHONE;
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function the_email_notification_parameter_on_counter_staff_should_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [[RequestKeys::STAFF_EMAIL_NOTIFICATION => 'A string']]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.' . RequestKeys::STAFF_EMAIL_NOTIFICATION;
        $request->assertValidationErrors([$attribute]);

        $request->assertValidationMessages([
            Lang::get('validation.boolean', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function the_sms_notification_parameter_on_counter_staff_should_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::COUNTER_STAFF => [[RequestKeys::STAFF_SMS_NOTIFICATION => 'A string']]]);

        $request->assertValidationFailed();
        $attribute = RequestKeys::COUNTER_STAFF . '.0.' . RequestKeys::STAFF_SMS_NOTIFICATION;
        $request->assertValidationErrors([$attribute]);

        $request->assertValidationMessages([
            Lang::get('validation.boolean', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_offers_delivery_parameter_should_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OFFERS_DELIVERY => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OFFERS_DELIVERY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::OFFERS_DELIVERY);
        $request->assertValidationMessages([
            Lang::get('validation.boolean', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_image_must_be_a_file()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGE => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::IMAGE);
        $request->assertValidationMessages([
            Lang::get('validation.file', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_image_must_be_an_image()
    {
        $file    = UploadedFile::fake()->create('test.txt');
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGE => $file]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGE]);
        $attribute  = $this->getDisplayableAttribute(RequestKeys::IMAGE);
        $validTypes = ['jpg', 'jpeg', 'png', 'gif', 'heic'];
        $request->assertValidationMessages([
            Lang::get('validation.mimes', ['attribute' => $attribute, 'values' => join(', ', $validTypes)]),
        ]);
    }

    /** @test */
    public function its_image_should_not_be_larger_than_5_megabytes()
    {
        $image   = UploadedFile::fake()->image('avatar.jpeg')->size(1024 * 11);
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGE => $image]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::IMAGE);
        $size      = 1024 * 5;
        $request->assertValidationMessages([
            Lang::get('validation.max.file', ['attribute' => $attribute, 'max' => $size]),
        ]);
    }

    /** @test */
    public function its_logo_must_be_a_file()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOGO => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOGO]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::LOGO);
        $request->assertValidationMessages([
            Lang::get('validation.file', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_logo_must_be_an_image()
    {
        $file    = UploadedFile::fake()->create('test.txt');
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOGO => $file]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOGO]);
        $attribute  = $this->getDisplayableAttribute(RequestKeys::LOGO);
        $validTypes = ['jpg', 'jpeg', 'png', 'gif', 'heic'];
        $request->assertValidationMessages([
            Lang::get('validation.mimes', ['attribute' => $attribute, 'values' => join(', ', $validTypes)]),
        ]);
    }

    /** @test */
    public function its_logo_should_not_be_larger_than_5_megabytes()
    {
        $logo    = UploadedFile::fake()->image('avatar.jpeg')->size(1024 * 11);
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOGO => $logo]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOGO]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::LOGO);
        $size      = 1024 * 5;
        $request->assertValidationMessages([
            Lang::get('validation.max.file', ['attribute' => $attribute, 'max' => $size]),
        ]);
    }

    /** @test */
    public function its_about_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ABOUT => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ABOUT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ABOUT);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $image = UploadedFile::fake()->image('image.jpeg');
        $logo  = UploadedFile::fake()->image('logo.jpeg');

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'supplier@email.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::PHONE            => '555222810',
            RequestKeys::PROKEEP_PHONE    => '555123456',
            RequestKeys::ADDRESS          => 'Main St.',
            RequestKeys::CITY             => 'City',
            RequestKeys::STATE            => 'US-AR',
            RequestKeys::COUNTRY          => 'US',
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::ZIP_CODE         => '12345',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555234567',
            RequestKeys::MANAGER_NAME     => 'Manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555888210',
            RequestKeys::ACCOUNTANT_NAME  => 'Accountant',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'               => 'Counter',
                    'email'              => 'counterstaff@email.com',
                    'phone'              => '7565486',
                    'email_notification' => true,
                    'sms_notification'   => true,
                ],
            ],
            RequestKeys::OFFERS_DELIVERY  => 1,
            RequestKeys::IMAGE            => $image,
            RequestKeys::LOGO             => $logo,
        ]);

        $request->assertValidationPassed();
    }
}
