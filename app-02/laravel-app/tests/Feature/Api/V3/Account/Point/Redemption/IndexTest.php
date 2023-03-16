<?php

namespace Tests\Feature\Api\V3\Account\Point\Redemption;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V3\Account\Point\Redemption\BaseResource;
use App\Models\Point;
use App\Models\User;
use App\Models\XoxoRedemption;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RedemptionController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_POINT_REDEMPTION_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_user_redemptions_sorted_by_newest()
    {
        $user = User::factory()->create();

        $xoxoRedemptions = XoxoRedemption::factory()->count(3)->sequence(fn(Sequence $sequence) => [
            'created_at' => Carbon::now()->addMinutes($sequence->index),
        ])->create();
        $xoxoRedemptions->each(function(XoxoRedemption $redemption) use ($user) {
            Point::factory()->usingUser($user)->usingXoxoRedemption($redemption)->redeemed()->create();
        });

        $anotherXoxoRedemptions = XoxoRedemption::factory()->count(2)->create();
        $anotherXoxoRedemptions->each(function(XoxoRedemption $redemption) {
            Point::factory()->usingXoxoRedemption($redemption)->redeemed()->create();
        });

        $expectedRedemptions = $xoxoRedemptions->reverse();

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data->each(function(array $rawPart) use ($expectedRedemptions) {
            $xoxoRedemption = $expectedRedemptions->shift();
            $this->assertSame($xoxoRedemption->getRouteKey(), $rawPart['id']);
        });
    }
}
