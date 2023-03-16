<?php

namespace Tests\Feature\Api\V3\Auth\Phone\Register\Assign;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\User\Created;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\AssignController;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Assign\InvokeRequest;
use App\Models\Phone;
use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see AssignController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_AUTH_PHONE_REGISTER_ASSIGN;

    /** @test */
    public function it_can_not_proceed_without_an_auth_token()
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->withoutExceptionHandling()->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_can_not_proceed_without_a_valid_phone_auth_token()
    {
        $this->login();

        $this->expectException(UnauthorizedHttpException::class);
        $this->withoutExceptionHandling()->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_should_return_forbidden_exception_on_unverified_phone_token()
    {
        Auth::shouldUse('phone');

        $phone = Phone::factory()->unverified()->create();
        $this->login($phone);

        $email = 'john@doe.com';

        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::EMAIL        => $email,
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_register_a_user_associates_the_provided_valid_phone_and_returns_a_valid_token_for_the_user()
    {
        Event::fake(Created::class);
        Auth::shouldUse('phone');

        $phone = Phone::factory()->verified()->create();
        $this->login($phone);

        $email = 'john@doe.com';

        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::EMAIL        => $email,
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(User::tableName(), [
            'email'                  => $email,
            'password'               => '',
            'phone'                  => $phone->fullNumber(),
            'terms'                  => 1,
            'registration_completed' => 1,
        ]);

        $user = User::where('email', $email)->first();

        $this->assertDatabaseHas(Phone::tableName(),
            ['country_code' => $phone->country_code, 'number' => $phone->number, 'user_id' => $user->getKey()]);
    }

    /** @test */
    public function it_register_that_the_user_accepts_the_terms_of_use()
    {
        Event::fake(Created::class);
        Auth::shouldUse('phone');

        $currentTerm = Term::factory()->create();

        $phone = Phone::factory()->verified()->create();
        $this->login($phone);

        $email = 'john@doe.com';

        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::EMAIL        => $email,
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $user = User::where('email', $email)->first();

        $this->assertDatabaseHas(TermUser::tableName(),
            ['user_id' => $user->getKey(), 'term_id' => $currentTerm->getKey()]);
    }

    /** @test */
    public function it_dispatches_an_event()
    {
        Event::fake(Created::class);
        Auth::shouldUse('phone');

        $phone = Phone::factory()->verified()->create();
        $this->login($phone);

        $email = 'john@doe.com';

        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::EMAIL        => $email,
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $user = User::where('email', $email)->first();

        Event::assertDispatched(function(Created $event) use ($user) {
            $property = new ReflectionProperty($event, 'user');
            $property->setAccessible(true);
            $this->assertSame($user->getKey(), $property->getValue($event)->getKey());

            return true;
        });
    }

    /**
     * @test
     */
    public function it_should_return_validation_exception_if_user_is_disabled()
    {
        Event::fake(Created::class);
        Auth::shouldUse('phone');

        $user  = User::factory()->create(['disabled_at' => Carbon::now()]);
        $phone = Phone::factory()->verified()->create();
        $this->login($phone);

        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::EMAIL        => $user->email,
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            RequestKeys::EMAIL => 'The account has been disabled.',
        ]);
    }
}
