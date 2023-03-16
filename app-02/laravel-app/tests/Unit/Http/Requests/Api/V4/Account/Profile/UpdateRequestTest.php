<?php

namespace Tests\Unit\Http\Requests\Api\V4\Account\Profile;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\Account\ProfileController;
use App\Http\Requests\Api\V4\Account\Profile\UpdateRequest;
use App\Models\Company;
use App\Models\User;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Auth;
use Illuminate\Http\UploadedFile;
use Lang;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\State;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see ProfileController */
class UpdateRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = UpdateRequest::class;

    /** @test */
    public function its_photo_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHOTO => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHOTO]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHOTO);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_photo_must_be_a_file()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHOTO => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHOTO]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHOTO);
        $request->assertValidationMessages([Lang::get('validation.file', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_photo_must_be_a_photo()
    {
        $file    = UploadedFile::fake()->create('test.txt');
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHOTO => $file]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHOTO]);
        $attribute  = $this->getDisplayableAttribute(RequestKeys::PHOTO);
        $validTypes = ['jpg', 'jpeg', 'png', 'gif', 'heic'];
        $request->assertValidationMessages([
            Lang::get('validation.mimes', ['attribute' => $attribute, 'values' => join(', ', $validTypes)]),
        ]);
    }

    /** @test */
    public function its_photo_should_not_be_larger_than_ten_megabytes()
    {
        $image   = UploadedFile::fake()->image('avatar.jpeg')->size(1024 * 11);
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHOTO => $image]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHOTO]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHOTO);
        $size      = 1024 * 10;
        $request->assertValidationMessages([
            Lang::get('validation.max.file', ['attribute' => $attribute, 'max' => $size]),
        ]);
    }

    /** @test */
    public function its_first_name_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::FIRST_NAME => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::FIRST_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::FIRST_NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_first_name_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::FIRST_NAME => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::FIRST_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::FIRST_NAME);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_last_name_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LAST_NAME => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LAST_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::LAST_NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_last_name_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LAST_NAME => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LAST_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::LAST_NAME);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_experience_may_be_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EXPERIENCE => null]);

        $request->assertValidationErrorsMissing([RequestKeys::EXPERIENCE]);
    }

    /** @test */
    public function its_experience_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EXPERIENCE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EXPERIENCE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::EXPERIENCE);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_public_name_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PUBLIC_NAME => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PUBLIC_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PUBLIC_NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_public_name_should_start_with_a_letter()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PUBLIC_NAME => '1PublicName']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PUBLIC_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PUBLIC_NAME);
        $request->assertValidationMessages([
            Str::replace(':attribute', $attribute, 'The :attribute must start with a letter.'),
        ]);
    }

    /** @test */
    public function its_public_name_can_contain_alpha_numeric_characters_and_dashes()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PUBLIC_NAME => '&*(ASDF']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PUBLIC_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PUBLIC_NAME);
        $request->assertValidationMessages([Lang::get('validation.alpha_dash', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_public_name_must_be_unique()
    {
        $this->refreshDatabaseForSingleTest();

        User::factory()->create(['public_name' => $publicName = 'PublicName']);

        $request = $this->formRequest($this->requestClass, [RequestKeys::PUBLIC_NAME => $publicName]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PUBLIC_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PUBLIC_NAME);
        $request->assertValidationMessages([Lang::get('validation.unique', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_allow_a_user_to_send_his_public_name()
    {
        $this->refreshDatabaseForSingleTest();

        $user = User::factory()->create(['public_name' => $publicName = 'PublicName']);

        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = $this->formRequest($this->requestClass, [RequestKeys::PUBLIC_NAME => $publicName]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_bio_may_be_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BIO => null]);

        $request->assertValidationErrorsMissing([RequestKeys::BIO]);
    }

    /** @test */
    public function its_bio_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BIO => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BIO]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::BIO);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::ADDRESS])]);
    }

    /** @test */
    public function its_address_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::ADDRESS])]);
    }

    /** @test */
    public function its_address_2_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS_2]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_country_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::COUNTRY])]);
    }

    /** @test */
    public function its_country_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::COUNTRY])]);
    }

    /** @test */
    public function its_country_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => 'invalid country']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::COUNTRY])]);
    }

    /** @test */
    public function its_state_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::STATE])]);
    }

    /** @test */
    public function its_state_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::STATE])]);
    }

    /** @test */
    public function its_state_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY => (new Earth())->getCountries()->first()->code,
            RequestKeys::STATE   => 'invalid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::STATE])]);
    }

    /** @test */
    public function its_city_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::CITY])]);
    }

    /** @test */
    public function its_city_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::CITY])]);
    }

    /** @test */
    public function its_zip_code_may_be_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => null]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => ['not string']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_size_must_be_exactly_5_digits()
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
    public function its_zip_code_can_have_trailing_zeros()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => '00001']);

        $request->assertValidationErrorsMissing([RequestKeys::ZIP_CODE]);
    }

    /** @test */
    public function its_company_id_must_exist()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY => 'fake-company-uuid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_job_title_require_if_company_parameter_is_present()
    {
        $this->refreshDatabaseForSingleTest();

        $company = Company::factory()->createQuietly();
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY => $company->getRouteKey()]);
        $request->assertValidationFailed();
        $attribute = $this->getDisplayableAttribute(RequestKeys::JOB_TITLE);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_job_title_should_be_a_string()
    {
        $this->refreshDatabaseForSingleTest();
        $company = Company::factory()->createQuietly();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::JOB_TITLE => ['array value'],
            RequestKeys::COMPANY   => $company->getRouteKey(),
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::JOB_TITLE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::JOB_TITLE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_primary_equipment_type_should_be_excluded_if_company_type_is_not_contractor()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'invalid',
        ]);

        $request->assertValidationErrorsMissing([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
    }

    /** @test */
    public function its_primary_equipment_type_should_be_a_string()
    {
        $this->refreshDatabaseForSingleTest();

        $company = Company::factory()->create();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => ['array value'],
            RequestKeys::COMPANY                => $company->getRouteKey(),
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PRIMARY_EQUIPMENT_TYPE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_primary_equipment_should_have_valid_value()
    {
        $this->refreshDatabaseForSingleTest();
        $company = Company::factory()->create();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'invalid',
            RequestKeys::COMPANY                => $company->getRouteKey(),
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PRIMARY_EQUIPMENT_TYPE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_hat_required_parameter_must_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HAT_REQUESTED => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::HAT_REQUESTED]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::HAT_REQUESTED);
        $request->assertValidationMessages([Lang::get('validation.boolean', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();
        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state   = $country->getStates()->first();
        $image   = UploadedFile::fake()->image('avatar.jpeg');
        $company = Company::factory()->create([
            'type' => CompanyDataType::TYPE_TRADE_SCHOOL,
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::PHOTO                  => $image,
            RequestKeys::FIRST_NAME             => 'John',
            RequestKeys::LAST_NAME              => 'Doe',
            RequestKeys::EXPERIENCE             => 4,
            RequestKeys::PUBLIC_NAME            => 'JohnDoe55',
            RequestKeys::BIO                    => 'Lorem ipsum',
            RequestKeys::ADDRESS                => '1313 Evergreen St.',
            RequestKeys::ADDRESS_2              => '',
            RequestKeys::COUNTRY                => $country->code,
            RequestKeys::STATE                  => $state->isoCode,
            RequestKeys::CITY                   => 'city',
            RequestKeys::ZIP_CODE               => '12345',
            RequestKeys::COMPANY                => $company->getRouteKey(),
            RequestKeys::JOB_TITLE              => 'Student',
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'Industrial',
        ]);

        $request->assertValidationPassed();
    }
}
