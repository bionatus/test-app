<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\OrderController;
use App\Http\Requests\Api\V4\Order\StoreRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Oem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see OrderController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->login($user);
        $this->oem      = Oem::factory()->createQuietly();
        $this->company1 = Company::factory()->create();
        $this->company2 = Company::factory()->create();
        CompanyUser::factory()->usingUser($user)->usingCompany($this->company2)->create();
        $this->route = URL::route(RouteNames::API_V4_ORDER_STORE);
    }

    /** @test */
    public function its_oem_parameter_can_be_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OEM => null],
            ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationErrorsMissing([RequestKeys::OEM]);
    }

    /** @test */
    public function its_oem_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OEM => ['INVALID_ID']],
            ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::OEM);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_oem_parameter_must_exist_if_not_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OEM => 'invalid_OEM'],
            ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::OEM])]);
    }

    /** @test */
    public function it_returns_its_oem()
    {
        $request = StoreRequest::create('', 'POST', [
            RequestKeys::OEM => $this->oem->getRouteKey(),
        ]);
        $this->assertEquals($this->oem->getRouteKey(), $request->oem()->getRouteKey());
    }

    /** @test */
    public function its_company_id_parameter_can_be_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY => null],
            ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationErrorsMissing([RequestKeys::COMPANY]);
    }

    /** @test */
    public function its_company_id_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY => ['INVALID_ID']],
            ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_id_parameter_must_be_related_to_the_user()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY => $this->company1->getRouteKey()],
            ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY]);
        $request->assertValidationMessages(['The company must be related to the user.']);
    }

    /** @test */
    public function it_returns_its_company()
    {
        $request = StoreRequest::create('', 'POST', [
            RequestKeys::COMPANY => $this->company2->getRouteKey(),
        ]);
        $this->assertEquals($this->company2->getKey(), $request->company()->getKey());
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::OEM     => $this->oem->getRouteKey(),
            RequestKeys::COMPANY => $this->company2->getRouteKey(),
        ], ['method' => 'post', 'route' => $this->route]);
        $request->assertValidationPassed();
    }
}
