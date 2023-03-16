<?php

namespace Tests\Feature\LiveApi\V1\User;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\UserController;
use App\Http\Resources\LiveApi\V1\User\BaseResource;
use App\Models\Staff;
use App\Models\SupplierUser;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see UserController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_USER_INDEX;

    /** @test */
    public function it_displays_list_of_unconfirmed_and_confirmed_users()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;

        $unconfirmedUsers = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->count(10)->create();
        $confirmedUsers   = SupplierUser::factory()->usingSupplier($supplier)->confirmed()->count(5)->create();

        $unconfirmedUserIds = $unconfirmedUsers->pluck('user_id');
        $confirmedUserIds   = $confirmedUsers->pluck('user_id');

        $route = URL::route($this->routeName);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::JsonSchema()), $response);

        $data                 = Collection::make($response->json('data'));
        $dataUnconfirmedUsers = Collection::make($data['unconfirmedUsers']);
        $dataConfirmedUsers   = Collection::make($data['confirmedUsers']);

        $this->assertCount($dataUnconfirmedUsers->count(), $unconfirmedUsers);
        $this->assertCount($dataConfirmedUsers->count(), $confirmedUsers);

        $dataUnconfirmedUsers->each(function($dataUnconfirmedUser, int $index) use ($unconfirmedUserIds) {
            $this->assertSame($unconfirmedUserIds[$index], $dataUnconfirmedUser['id']);
        });

        $dataConfirmedUsers->each(function($dataConfirmedUser, int $index) use ($confirmedUserIds) {
            $this->assertSame($confirmedUserIds[$index], $dataConfirmedUser['id']);
        });
    }
}
