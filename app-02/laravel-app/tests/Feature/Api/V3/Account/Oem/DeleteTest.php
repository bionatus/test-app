<?php

namespace Tests\Feature\Api\V3\Account\Oem;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\OemController;
use App\Models\Oem;
use App\Models\OemUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OemController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_OEM_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName, Oem::factory()->create()));
    }

    /** @test */
    public function it_deletes_user_favorite_oem()
    {
        $user = User::factory()->create();
        $oem  = Oem::factory()->create();

        OemUser::factory()->usingUser($user)->usingOem($oem)->create();

        $this->login($user);

        $response = $this->delete(URL::route($this->routeName, $oem));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(OemUser::tableName(), [
            'user_id' => $user->getKey(),
            'oem_id'  => $oem->getKey(),
        ]);
    }
}
