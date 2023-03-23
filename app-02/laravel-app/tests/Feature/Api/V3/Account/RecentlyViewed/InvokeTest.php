<?php

namespace Tests\Feature\Api\V3\Account\RecentlyViewed;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\RecentlyViewedController;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RecentlyViewedController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_RECENTLY_VIEWED;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_recently_viewed_data()
    {
        $user = User::factory()->create();

        PartDetailCounter::factory()->count(4)->create();
        OemDetailCounter::factory()->count(4)->create();

        $objectViewedOne   = PartDetailCounter::factory()->usingUser($user)->create([
            'created_at' => Carbon::now()->subSeconds(7),
        ]);
        $objectViewedTwo   = PartDetailCounter::factory()->usingUser($user)->create([
            'created_at' => Carbon::now(),
        ]);
        $objectViewedThree = OemDetailCounter::factory()->usingUser($user)->create([
            'created_at' => Carbon::now()->subSeconds(20),
        ]);
        $objectViewedFour  = OemDetailCounter::factory()->usingUser($user)->create([
            'created_at' => Carbon::now()->subSeconds(10),
        ]);

        $expectedObjects = Collection::make([
            $objectViewedTwo->part,
            $objectViewedOne->part,
            $objectViewedFour->oem,
            $objectViewedThree->oem,
        ]);

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $data     = Collection::make($response->json('data'));

        $data->each(function(array $rawViewed, int $index) use ($expectedObjects) {
            $this->assertSame($expectedObjects->get($index)::MORPH_ALIAS, $rawViewed['type']);
            if ($rawViewed['type'] === Oem::MORPH_ALIAS) {
                $this->assertSame($expectedObjects->get($index)->getRouteKey(), $rawViewed['info']['id']);
            } elseif ($rawViewed['type'] === Part::MORPH_ALIAS) {
                $this->assertSame($expectedObjects->get($index)->item->getRouteKey(), $rawViewed['info']['id']);
            }
        });
    }
}
