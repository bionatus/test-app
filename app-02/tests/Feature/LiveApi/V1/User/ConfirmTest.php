<?php

namespace Tests\Feature\LiveApi\V1\User;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\User\ConfirmedBySupplier;
use App\Http\Controllers\LiveApi\V1\ConfirmedUserController;
use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ConfirmedUserController */
class ConfirmTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_CONFIRMED_USER_CONFIRM;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $user = User::factory()->create();
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);
        $this->post(URL::route($this->routeName, [RouteParameters::USER => $user]),
            [RequestKeys::CUSTOMER_TIER => 'Preferred', RequestKeys::CASH_BUYER => true]);
    }

    /** @test */
    public function it_updates_supplier_user_from_unconfirmed_to_confirmed_status()
    {
        Event::fake(ConfirmedBySupplier::class);

        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->create();

        Supplier::flushEventListeners();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $response = $this->post($route, [
            RequestKeys::CUSTOMER_TIER => $customerTier = 'Preferred',
            RequestKeys::CASH_BUYER    => true,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(ExtendedSupplierUserResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'status'        => SupplierUser::STATUS_CONFIRMED,
            'customer_tier' => $customerTier,
            'cash_buyer'    => true,
        ]);
    }

    /** @test */
    public function it_dispatches_a_confirmed_event()
    {
        Event::fake(ConfirmedBySupplier::class);

        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $this->post($route, [
            RequestKeys::CUSTOMER_TIER => 'Preferred',
            RequestKeys::CASH_BUYER    => true,
        ]);

        Event::assertDispatched(ConfirmedBySupplier::class);
    }
}
