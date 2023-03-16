<?php

namespace Tests\Feature\Api\V3\Account\Oem;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\OemController;
use App\Http\Resources\Api\V3\Account\Oem\BaseResource;
use App\Models\Oem;
use App\Models\OemUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_OEM_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_a_list_of_oems_filtered_by_user()
    {
        $user     = User::factory()->create();
        $oem      = Oem::factory()->create();
        $expected = OemUser::factory()->usingUser($user)->usingOem($oem)->count(2)->create();
        OemUser::factory()->count(3)->create();

        $this->login($user);

        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data->each(function(array $rawOemUser) use ($expected) {
            $oemUser = $expected->shift();
            $this->assertSame($oemUser->oem->getRouteKey(), $rawOemUser['id']);
        });
    }

    /** @test */
    public function it_does_not_show_not_live_oems()
    {
        $user = User::factory()->create();

        $expected = OemUser::factory()
            ->usingUser($user)
            ->usingOem(Oem::factory()->live()->create())
            ->count(3)
            ->create();
        OemUser::factory()->usingUser($user)->usingOem(Oem::factory()->pending()->create())->count(2)->create();

        $this->login($user);

        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data->each(function(array $rawOemUser) use ($expected) {
            $oemUser = $expected->shift();
            $this->assertSame($oemUser->oem->getRouteKey(), $rawOemUser['id']);
        });
    }

    /** @test */
    public function it_display_a_list_of_oems_user_favorite_ordered_newest()
    {
        $user = User::factory()->create();

        $oemFirst = Oem::factory()->create();
        OemUser::factory()->usingUser($user)->usingOem($oemFirst)->create([
            'created_at' => Carbon::now()->addSeconds(),
        ]);

        $oemSecond = Oem::factory()->create();
        OemUser::factory()->usingUser($user)->usingOem($oemSecond)->create();

        $oemThird = Oem::factory()->create();
        OemUser::factory()->usingUser($user)->usingOem($oemThird)->create([
            'created_at' => Carbon::now()->addSeconds(3),
        ]);

        OemUser::factory()->count(3)->create();

        $oemsExpected = collection::make([
            $oemThird,
            $oemFirst,
            $oemSecond,
        ]);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawOem, int $index) use ($oemsExpected) {
            $this->assertEquals($oemsExpected->get($index)->getRouteKey(), $rawOem['id']);
        });
    }
}
