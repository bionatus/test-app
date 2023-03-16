<?php

namespace Tests\Feature\LiveApi\V1\User\RemovedUser;

use App\Constants\RouteNames;
use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierUser;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_REMOVED_USER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_removed_users()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        $expectedRemovedUsers = SupplierUser::factory()->usingSupplier($supplier)->removed()->count(5)->create();

        $route = URL::route($this->routeName);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(ExtendedSupplierUserResource::jsonSchema(), true), $response);
        $data->each(function($rawRemovedUser, int $index) use ($expectedRemovedUsers) {
            $removedUser = $expectedRemovedUsers->get($index);
            $this->assertSame($removedUser->user->getRouteKey(), $rawRemovedUser['id']);
        });
    }
}
