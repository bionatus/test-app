<?php

namespace Tests\Feature\LiveApi\V1\Supplier\User;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Supplier\UserController;
use App\Http\Resources\LiveApi\V1\Supplier\User\BaseResource;
use App\Models\PubnubChannel;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see UserController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_SUPPLIER_USER_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->create();
        $route         = URL::route($this->routeName, [RouteParameters::CHANNEL => $pubnubChannel->channel]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_displays_a_user()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $user          = User::factory()->create();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();
        $route         = URL::route($this->routeName, [RouteParameters::CHANNEL => $pubnubChannel]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $user->getKey());
    }
}
