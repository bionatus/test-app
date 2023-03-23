<?php

namespace Tests\Feature\Api\V3\Point\XoxoVoucher;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Point\XoxoVoucherController;
use App\Http\Resources\Api\V3\Point\XoxoVoucher\DetailedResource;
use App\Models\User;
use App\Models\XoxoVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see XoxoVoucherController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_POINTS_VOUCHERS_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName,
            [RouteParameters::VOUCHER => XoxoVoucher::factory()->create()->getRouteKey()]));
    }

    /** @test */
    public function it_displays_a_voucher()
    {
        $user        = User::factory()->create();
        $xoxoVoucher = XoxoVoucher::factory()->create();
        XoxoVoucher::factory()->count(3)->create();

        $this->login($user);
        $route = URL::route($this->routeName, $xoxoVoucher);

        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertSame($data['id'], $xoxoVoucher->getRouteKey());
    }
}

