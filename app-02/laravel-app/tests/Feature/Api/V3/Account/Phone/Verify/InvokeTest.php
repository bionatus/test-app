<?php

namespace Tests\Feature\Api\V3\Account\Phone\Verify;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\Phone\Verified;
use App\Http\Requests\Api\V3\Account\Phone\Verify\InvokeRequest;
use App\Http\Resources\Api\V3\Account\Phone\Verify\BaseResource;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\User;
use Event;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_PHONE_VERIFY;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $phone = Phone::factory()->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, $phone->fullNumber()));
    }

    /** @test
     * @throws Exception
     */
    public function it_verifies_a_phone()
    {
        $phone              = Phone::factory()->create(['id' => 200]);
        $authenticationCode = AuthenticationCode::factory()->usingPhone($phone)->verification()->create();

        $this->login();
        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $authenticationCode->code,
        ]);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $response->assertStatus(Response::HTTP_CREATED);

        $data = $response->json('data');

        $this->assertSame($phone->fullNumber(), $data['id']);
    }

    /** @test
     * @throws Exception
     */
    public function it_removes_verification_type_authentication_codes_after_successful_verification()
    {
        $phone               = Phone::factory()->create();
        $authenticationCode  = AuthenticationCode::factory()->usingPhone($phone)->verification()->create();
        $authenticationCodes = AuthenticationCode::factory()->usingPhone($phone)->count(2)->verification()->create();
        $authenticationCodes->push($authenticationCode);
        AuthenticationCode::factory()->usingPhone($phone)->count(2)->login()->create();

        $this->login();
        $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $authenticationCode->code,
        ]);

        $authenticationCodes->each(function(AuthenticationCode $authenticationCode) {
            $this->assertDatabaseMissing(AuthenticationCode::tableName(), ['id' => $authenticationCode->getKey()]);
        });

        $this->assertCount(2, AuthenticationCode::all());
    }

    /** @test */
    public function it_dispatch_an_event()
    {
        Event::fake(Verified::class);
        $phone              = Phone::factory()->create(['id' => 200]);
        $authenticationCode = AuthenticationCode::factory()->usingPhone($phone)->verification()->create();

        $this->login();
        $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $authenticationCode->code,
        ]);

        Event::assertDispatched(function(Verified $event) use ($phone) {
            $this->assertSame($phone->getKey(), $event->phone()->getKey());

            return true;
        });
    }

    /** @test */
    public function it_associates_the_logged_user_with_the_phone()
    {
        $user               = User::factory()->create();
        $phone              = Phone::factory()->create(['id' => 55]);
        $authenticationCode = AuthenticationCode::factory()->usingPhone($phone)->verification()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $authenticationCode->code,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $phone->refresh();

        $this->assertSame($user->getKey(), $phone->user->getKey());
    }
}
