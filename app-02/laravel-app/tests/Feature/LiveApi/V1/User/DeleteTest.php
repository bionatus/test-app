<?php

namespace Tests\Feature\LiveApi\V1\User;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\User\UnconfirmedBySupplier;
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
class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_CONFIRMED_USER_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $user = User::factory()->create();
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);
        $this->delete(URL::route($this->routeName, [RouteParameters::USER => $user]));
    }

    /** @test */
    public function it_updates_supplier_user_from_confirmed_to_unconfirmed()
    {
        Event::fake(UnconfirmedBySupplier::class);

        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->create();

        Supplier::flushEventListeners();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ExtendedSupplierUserResource::jsonSchema()), $response);

        $this->assertDatabaseHas(SupplierUser::tableName(), ['status' => SupplierUser::STATUS_UNCONFIRMED]);
    }

    /** @test */
    public function it_reset_cash_buyer_and_customer_tier()
    {
        Event::fake(UnconfirmedBySupplier::class);

        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->create([
            'customer_tier' => 'Preferred',
            'cash_buyer'    => true,
        ]);

        Supplier::flushEventListeners();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ExtendedSupplierUserResource::jsonSchema()), $response);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'status'        => SupplierUser::STATUS_UNCONFIRMED,
            'customer_tier' => null,
            'cash_buyer'    => 0,
        ]);
    }

    /** @test */
    public function it_dispatches_an_unconfirmed_event()
    {
        Event::fake(UnconfirmedBySupplier::class);

        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->create();

        Supplier::flushEventListeners();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $this->delete($route);

        Event::assertDispatched(UnconfirmedBySupplier::class);
    }
}
