<?php

namespace Tests\Feature\BasecampApi\V1\SupportCall;

use Config;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\BasecampApi\V1\SupportCallController;
use App\Http\Resources\BasecampApi\V1\SupportCall\BaseResource;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see SupportCallController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::BASECAMP_API_V1_SUPPORT_CALL_SHOW;

    /** @test */
    public function it_display_a_support_call()
    {

        $oem  = Oem::factory()->create(['refrigerant' => 'refrigerant']);
        $user = User::factory()->create();

        $supportCall = SupportCall::factory()->usingUser($user)->usingOem($oem)->create();

        $route = URL::route($this->routeName, [
            RouteParameters::SUPPORT_CALL => $supportCall->getRouteKey(),
        ]);

        Config::set('basecamp.token.key', $key = 'test_key');
        $token    = Hash::make($key);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());

        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $supportCall->getRouteKey());
    }
}
