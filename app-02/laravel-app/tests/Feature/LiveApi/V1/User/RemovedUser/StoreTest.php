<?php

namespace Tests\Feature\LiveApi\V1\User\RemovedUser;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\User\RemovedBySupplier;
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

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_REMOVED_USER_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, [RouteParameters::USER => User::factory()->create()]));
    }

    /** @test */
    public function it_removes_a_user()
    {
        Event::fake(RemovedBySupplier::class);

        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(ExtendedSupplierUserResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupplierUser::tableName(), ['status' => SupplierUser::STATUS_REMOVED]);
    }

    /** @test */
    public function it_dispatches_a_removed_by_supplier_event()
    {
        Event::fake(RemovedBySupplier::class);

        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $this->post($route);

        Event::assertDispatched(RemovedBySupplier::class);
    }
}
