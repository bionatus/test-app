<?php

namespace Tests\Feature\Api\V3\Account\Point;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\PointController;
use App\Http\Resources\Api\V3\Account\Point\BaseResource;
use App\Models\AppSetting;
use App\Models\Point;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PointController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_POINT_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_points_data()
    {
        $user = User::factory()->create();
        AppSetting::factory()->create(['slug' => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER, 'value' => $multiplier = 2]);
        Point::factory()->usingUser($user)->createQuietly(['points_earned' => $earnedPoints = 100]);
        Point::factory()->usingUser($user)->redeemed()->createQuietly(['points_redeemed' => $redeemedPoints = 30]);
        $availablePoints = $earnedPoints - $redeemedPoints;
        $cashValueRate   = 0.01;
        $cashValue       = round($availablePoints * $cashValueRate, 2);

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $data = $response->json('data');
        $this->assertEquals($availablePoints, $data['available_points']);
        $this->assertEquals($earnedPoints, $data['earned_points']);
        $this->assertEquals($cashValue, $data['cash_value']);
        $this->assertEquals($multiplier, $data['multiplier']);
        $this->assertEquals(true, $data['support_call_enabled']);
    }
}
