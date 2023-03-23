<?php

namespace Tests\Feature\Api\V3\Account\Oem;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\OemController;
use App\Http\Requests\Api\V3\Account\Oem\StoreRequest;
use App\Models\Oem;
use App\Models\OemUser;
use App\Models\User;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\CanRefreshDatabase;
use Tests\TestCase;
use URL;

/** @see OemController */
class StoreTest extends TestCase
{
    use CanRefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_OEM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->refreshDatabaseForSingleTest();
        
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::OEM => Oem::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_saves_user_favorite_oem()
    {
        $this->refreshDatabaseForSingleTest();

        $user = User::factory()->create();
        $oem  = Oem::factory()->create();

        $this->login($user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::OEM => $oem->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(OemUser::tableName(), [
            'user_id' => $user->getKey(),
            'oem_id'  => $oem->getKey(),
        ]);
    }
}
