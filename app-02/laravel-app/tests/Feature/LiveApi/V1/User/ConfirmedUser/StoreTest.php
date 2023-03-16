<?php

namespace Tests\Feature\LiveApi\V1\User\ConfirmedUser;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\User\ConfirmedUserController;
use App\Http\Requests\LiveApi\V1\User\SupplierUser\StoreRequest;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ConfirmedUserController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_USER_CONFIRM_USER_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $user  = User::factory()->create();
        $route = URL::route($this->routeName, [RouteParameters::USER => $user]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_updates_the_supplier_user()
    {
        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $user     = User::factory()->create();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->unconfirmed()->create();
        $route = URL::route($this->routeName, [RouteParameters::USER => $user]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->post($route, [
            RequestKeys::CASH_BUYER    => $cashBuyer = false,
            RequestKeys::CUSTOMER_TIER => $customerTier = 'test customer tier',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'user_id'       => $user->getKey(),
            'supplier_id'   => $supplier->getKey(),
            'cash_buyer'    => $cashBuyer,
            'customer_tier' => $customerTier,
            'status'        => 'confirmed',
        ]);
    }
}
