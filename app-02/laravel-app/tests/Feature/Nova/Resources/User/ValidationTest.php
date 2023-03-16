<?php

namespace Tests\Feature\Nova\Resources\User;

use App\Constants\RequestKeys;
use App\Nova\Resources\User;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . User::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_validation_on_valid_data_on_creation(array $data)
    {
        $response = $this->postJson($this->path, $data);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_validation_on_valid_data_on_update(array $data)
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->postJson($this->path . $user->getKey(), array_merge(['_method' => 'PUT'], $data));
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }

    public function dataProvider(): array
    {
        return [
            [
                [
                    'first_name'                  => 'John',
                    'last_name'                   => 'Doe',
                    'email'                       => 'john@doe.com',
                    'password'                    => 'password',
                    'address'                     => '',
                    'address_2'                   => '',
                    'country'                     => '',
                    'state'                       => '',
                    'zip'                         => '',
                    'city'                        => '',
                    RequestKeys::COMPANY_NAME     => '',
                    RequestKeys::COMPANY_TYPE     => '',
                    RequestKeys::JOB_TITLE        => '',
                    RequestKeys::COMPANY_COUNTRY  => '',
                    RequestKeys::COMPANY_STATE    => '',
                    RequestKeys::COMPANY_ZIP_CODE => '',
                    RequestKeys::COMPANY_ADDRESS  => '',
                    RequestKeys::COMPANY_CITY     => '',
                ],
            ],
            [
                [
                    'first_name'                  => 'John',
                    'last_name'                   => 'Doe',
                    'email'                       => 'john@doe.com',
                    'password'                    => 'password',
                    'address'                     => '',
                    'address_2'                   => '',
                    'country'                     => '',
                    'state'                       => '',
                    'zip'                         => '',
                    'city'                        => '',
                    RequestKeys::COMPANY_NAME     => 'My Company',
                    RequestKeys::COMPANY_TYPE     => CompanyDataType::TYPE_OEM,
                    RequestKeys::JOB_TITLE        => 'Engineer',
                    RequestKeys::COMPANY_COUNTRY  => CountryDataType::UNITED_STATES,
                    RequestKeys::COMPANY_STATE    => 'US-NY',
                    RequestKeys::COMPANY_ZIP_CODE => 90210,
                    RequestKeys::COMPANY_ADDRESS  => 'the new address',
                    RequestKeys::COMPANY_CITY     => 'Beverly Hills',
                ],
            ],
            [
                [
                    'first_name'                        => 'John',
                    'last_name'                         => 'Doe',
                    'email'                             => 'john@doe.com',
                    'password'                          => 'password',
                    'address'                           => '',
                    'address_2'                         => '',
                    'country'                           => '',
                    'state'                             => '',
                    'zip'                               => '',
                    'city'                              => '',
                    RequestKeys::COMPANY_NAME           => 'My Company',
                    RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
                    RequestKeys::PRIMARY_EQUIPMENT_TYPE => CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL,
                    RequestKeys::JOB_TITLE              => 'Installer',
                    RequestKeys::COMPANY_COUNTRY        => CountryDataType::UNITED_STATES,
                    RequestKeys::COMPANY_STATE          => 'US-NY',
                    RequestKeys::COMPANY_ZIP_CODE       => 90210,
                    RequestKeys::COMPANY_ADDRESS        => 'the new address',
                    RequestKeys::COMPANY_CITY           => 'Beverly Hills',
                ],
            ],
            [
                [
                    'first_name'                  => 'John',
                    'last_name'                   => 'Doe',
                    'email'                       => 'john@doe.com',
                    'password'                    => 'password',
                    'address'                     => '',
                    'address_2'                   => '',
                    'country'                     => 'US',
                    'state'                       => '',
                    'zip'                         => '',
                    'city'                        => '',
                    RequestKeys::COMPANY_NAME     => '',
                    RequestKeys::COMPANY_TYPE     => '',
                    RequestKeys::JOB_TITLE        => '',
                    RequestKeys::COMPANY_COUNTRY  => '',
                    RequestKeys::COMPANY_STATE    => '',
                    RequestKeys::COMPANY_ZIP_CODE => '',
                    RequestKeys::COMPANY_ADDRESS  => '',
                    RequestKeys::COMPANY_CITY     => '',
                ],
            ],
        ];
    }

    /** @test */
    public function its_email_must_be_unique_when_creating()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->postJson($this->path, ['email' => $user->email]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => Lang::get('validation.unique', ['attribute' => 'Email']),
        ]);
    }

    /** @test */
    public function its_email_must_be_unique_when_updating()
    {
        $otherUser = \App\Models\User::factory()->create();

        $user = \App\Models\User::factory()->create();

        $response = $this->putJson($this->path . $user->getKey(), ['email' => $otherUser->email]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'email' => Lang::get('validation.unique', ['attribute' => 'Email']),
        ]);
    }

    /** @test */
    public function its_email_can_be_the_same_when_updating()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->putJson($this->path . $user->getKey(), ['email' => $user->email]);

        $response->assertJsonMissingValidationErrors(['email']);
    }

    /** @test */
    public function its_country_must_be_on_valid_countries_list()
    {
        $response = $this->postJson($this->path, ['country' => 'ES']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'country' => Lang::get('validation.in', ['attribute' => 'Country']),
        ]);
    }

    /** @test */
    public function its_country_must_be_required_if_state_is_sent()
    {
        $response = $this->postJson($this->path, ['state' => 'any']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'country' => Lang::get('validation.required_with', ['attribute' => 'Country', 'values' => 'State']),
        ]);
    }

    /** @test */
    public function its_state_must_be_a_valid_state_for_the_country()
    {
        $response = $this->postJson($this->path, ['country' => 'US', 'state' => 'invalid']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'state' => Lang::get('validation.in', ['attribute' => 'State']),
        ]);
    }

    /** @test */
    public function its_state_can_be_null()
    {
        $response = $this->postJson($this->path, ['country' => 'US', 'state' => '']);

        $response->assertJsonMissingValidationErrors(['state']);
    }

    /** @test */
    public function its_company_type_must_be_valid()
    {
        $response = $this->postJson($this->path, [RequestKeys::COMPANY_TYPE => 'invalid']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            RequestKeys::COMPANY_TYPE => Lang::get('validation.in', ['attribute' => 'Type']),
        ]);
    }

    /** @test */
    public function its_primary_equipment_type_must_be_required_if_company_type_is_contractor()
    {
        $response = $this->postJson($this->path, [
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => '',
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => Lang::get('validation.required_if', [
                'attribute' => 'What equipment do you primarily work on?',
                'other'     => 'Type',
                'value'     => CompanyDataType::TYPE_CONTRACTOR,
            ]),
        ]);
    }

    /** @test */
    public function its_primary_equipment_type_must_be_valid()
    {
        $response = $this->postJson($this->path, [
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'invalid',
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => Lang::get('validation.in',
                ['attribute' => 'What equipment do you primarily work on?']),
        ]);
    }

    /** @test */
    public function its_job_title_must_be_valid()
    {
        $response = $this->postJson($this->path, [
            RequestKeys::COMPANY_TYPE => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::JOB_TITLE    => 'invalid',
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            RequestKeys::JOB_TITLE => Lang::get('validation.in', ['attribute' => 'Job Title']),
        ]);
    }

    /** @test */
    public function its_company_country_must_be_on_valid_countries_list()
    {
        $response = $this->postJson($this->path, [RequestKeys::COMPANY_COUNTRY => 'ES']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            RequestKeys::COMPANY_COUNTRY => Lang::get('validation.in', ['attribute' => 'Country']),
        ]);
    }

    /** @test */
    public function its_company_state_must_be_a_valid_state_for_the_company_country()
    {
        $response = $this->postJson($this->path,
            [RequestKeys::COMPANY_COUNTRY => 'US', RequestKeys::COMPANY_STATE => 'invalid']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            RequestKeys::COMPANY_STATE => Lang::get('validation.in', ['attribute' => 'State']),
        ]);
    }
}
