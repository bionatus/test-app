<?php

namespace Tests\Feature\LiveApi\V1\User\RemovedUser;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\User\RestoredBySupplier;
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

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_REMOVED_USER_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName, [RouteParameters::USER => User::factory()->create()]));
    }

    /** @test */
    public function it_restores_a_user()
    {
        Event::fake(RestoredBySupplier::class);

        $staff        = Staff::factory()->createQuietly();
        $supplier     = $staff->supplier;
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->removed()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseHas(SupplierUser::tableName(), ['status' => SupplierUser::STATUS_UNCONFIRMED]);
    }

    /** @test */
    public function it_dispatches_a_restored_by_supplier_event()
    {
        Event::fake(RestoredBySupplier::class);

        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $supplierUser = SupplierUser::factory()->usingSupplier($supplier)->removed()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [RouteParameters::USER => $supplierUser->user]);

        $this->delete($route);

        Event::assertDispatched(RestoredBySupplier::class);
    }
}
