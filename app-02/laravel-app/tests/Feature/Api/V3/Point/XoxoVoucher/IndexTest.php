<?php

namespace Tests\Feature\Api\V3\Point\XoxoVoucher;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Point\XoxoVoucherController;
use App\Http\Resources\Api\V3\Point\XoxoVoucher\BaseResource;
use App\Models\User;
use App\Models\XoxoVoucher;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see XoxoVoucherController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_POINTS_VOUCHERS_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_xoxo_vouchers_published()
    {
        $xoxoVouchers = XoxoVoucher::factory()->published()->count(20)->create();
        XoxoVoucher::factory()->unpublished()->count(5)->create();

        $this->login(User::factory()->create());
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $xoxoVouchers);

        $expectedData = $xoxoVouchers->take(15)->pluck(XoxoVoucher::routeKeyName());
        $data         = Collection::make($response->json('data'))->pluck(XoxoVoucher::keyName());

        $this->assertEquals($expectedData, $data);
    }

    /** @test */
    public function it_displays_a_list_of_xoxo_vouchers_sorted_by_sort_field_with_null_at_last()
    {
        $xoxoVouchersWithoutSort = XoxoVoucher::factory()->published()->count(3)->create(['sort' => null]);
        $xoxoVouchersWithSort    = XoxoVoucher::factory()->published()->count(5)->sequence(fn(Sequence $sequence
        ) => ['sort' => rand(1, 999)])->create();

        $expected = $xoxoVouchersWithSort->sortBy('sort')->merge($xoxoVouchersWithoutSort);

        $this->login(User::factory()->create());
        $response = $this->get(URL::route($this->routeName));
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);

        $data->each(function(array $rawXoxoVoucher, int $index) use ($expected) {
            $xoxoVoucher = $expected->get($index);
            $this->assertSame($xoxoVoucher->getRouteKey(), $rawXoxoVoucher['id']);
        });
    }

    /** @test */
    public function it_displays_a_list_of_xoxo_vouchers_sorted_by_sort_and_id()
    {
        $expected = XoxoVoucher::factory()
            ->published()
            ->count(5)
            ->sequence(fn(Sequence $sequence) => ['sort' => 1])
            ->create();

        $this->login(User::factory()->create());
        $response = $this->get(URL::route($this->routeName));
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);

        $data->each(function(array $rawXoxoVoucher, int $index) use ($expected) {
            $xoxoVoucher = $expected->get($index);
            $this->assertSame($xoxoVoucher->getRouteKey(), $rawXoxoVoucher['id']);
        });
    }
}
