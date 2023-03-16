<?php

namespace Tests\Feature\LiveApi\V2\Supplier;

use App\Constants\RouteNames\LiveApiV2;
use App\Http\Controllers\LiveApi\V2\SupplierController;
use App\Http\Resources\LiveApi\V2\Supplier\BaseResource;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SupplierController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = LiveApiV2::LIVE_API_V2_SUPPLIER_SHOW;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create([
            'name'          => "Bid Number Required",
            'slug'          => Setting::SLUG_BID_NUMBER_REQUIRED,
            'group'         => Setting::GROUP_VALIDATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ]);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_display_a_supplier()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();
        Supplier::flushEventListeners();
        $route = URL::route($this->routeName, $supplier);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $supplier->getRouteKey());
    }

    /** @test */
    public function it_updates_the_supplier_welcome_displayed_at_field()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();
        Supplier::flushEventListeners();
        $route = URL::route($this->routeName, $supplier);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $response->assertJsonPath('data.welcome_displayed_at', null);

        $this->assertNotNull($supplier->refresh()->welcome_displayed_at);
    }
}
