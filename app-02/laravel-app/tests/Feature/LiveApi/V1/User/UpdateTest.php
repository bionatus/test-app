<?php

namespace Tests\Feature\LiveApi\V1\User;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\LiveApi\V1\ConfirmedUser\UpdateRequest;
use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use App\Models\Staff;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_CONFIRMED_USER_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $user = User::factory()->create();
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);
        $this->patch(URL::route($this->routeName, [RouteParameters::USER => $user]),
            [RequestKeys::CUSTOMER_TIER => 'Preferred', RequestKeys::CASH_BUYER => true]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_updates_confirmed_supplier_user()
    {
        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->create([
            'customer_tier' => 'initial tier',
            'cash_buyer'    => true,
        ]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]), [
            RequestKeys::CUSTOMER_TIER => $customerTier = 'Gold',
            RequestKeys::CASH_BUYER    => false,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ExtendedSupplierUserResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'status'        => SupplierUser::STATUS_CONFIRMED,
            'customer_tier' => $customerTier,
            'cash_buyer'    => false,
        ]);
    }
}
