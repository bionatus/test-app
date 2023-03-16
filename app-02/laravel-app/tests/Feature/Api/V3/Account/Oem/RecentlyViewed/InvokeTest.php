<?php

namespace Tests\Feature\Api\V3\Account\Oem\RecentlyViewed;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Oem\RecentlyViewedController;
use App\Http\Resources\Api\V3\Account\Oem\RecentlyViewed\BaseResource;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RecentlyViewedController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_OEM_RECENTLY_VIEWED_INDEX;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_returns_a_list_of_oems_recently_viewed_by_user()
    {
        $user       = User::factory()->create();
        $other_user = User::factory()->create();
        $oem        = Oem::factory()->create(['model' => 'RGDJ-10EBRGA']);
        $other_oem  = Oem::factory()->create(['model' => 'RGDJ-10EBRGA second']);
        $now        = Carbon::now();

        OemDetailCounter::factory()->usingUser($user)->usingOem($oem)->create(['created_at' => $now]);
        OemDetailCounter::factory()->usingUser($user)->usingOem($oem)->create(['created_at' => $now->addDay()]);
        $oemDetailCounter3 = OemDetailCounter::factory()
            ->usingUser($user)
            ->usingOem($oem)
            ->create(['created_at' => $now->addDay()]);

        OemDetailCounter::factory()->usingUser($other_user)->usingOem($oem)->create(['created_at' => $now->addDay()]);
        OemDetailCounter::factory()
            ->usingUser($other_user)
            ->usingOem($other_oem)
            ->create(['created_at' => $now->addDay()]);

        $expected = Collection::make([$oemDetailCounter3]);

        $this->login($user);

        $route    = URL::route($this->routeName);
        $response = $this->get($route);

        $data = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data->each(function(array $rawOemUser) use ($expected) {
            $oemUser = $expected->shift();
            $this->assertSame($oemUser->oem->getRouteKey(), $rawOemUser['id']);
            $this->assertSame($oemUser->created_at->format('Y-m-d H:i:s'), $rawOemUser['visited_at']);
        });
    }
}
