<?php

namespace Tests\Feature\Api\V3\Account\PubnubChannel;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\PubnubChannelController;
use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PubnubChannelController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_CHANNEL_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_pubnub_channels()
    {
        $user           = User::factory()->create();
        $supplier1      = Supplier::factory()->createQuietly();
        $supplier2      = Supplier::factory()->createQuietly();
        $supplier3      = Supplier::factory()->createQuietly();
        $pubnubChannel1 = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier1)->create();
        $pubnubChannel2 = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier2)->create();
        $pubnubChannel3 = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier3)->create();
        PubnubChannel::factory()->count(2)->usingSupplier($supplier1)->create();
        $expectedPubnubChannelsRouteKeys = [
            $pubnubChannel1->getRouteKey(),
            $pubnubChannel2->getRouteKey(),
            $pubnubChannel3->getRouteKey(),
        ];

        $this->login($user);
        $response                       = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);

        $data                           = Collection::make($response->json('data'));
        $currentPubnubChannelsRouteKeys = $data->pluck(PubnubChannel::routeKeyName())->toArray();

        $this->assertEqualsCanonicalizing($expectedPubnubChannelsRouteKeys, $currentPubnubChannelsRouteKeys);
    }
}
