<?php

namespace Tests\Feature\LiveApi\V1\User\NewMessage;

use App;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\User\NewMessage;
use App\Http\Controllers\LiveApi\V1\User\NewMessageController;
use App\Http\Requests\LiveApi\V1\User\NewMessage\InvokeRequest;
use App\Models\Staff;
use App\Models\User;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use PubNub\PubNub;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see NewMessageController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_USER_NEW_MESSAGE;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $user  = User::factory()->create();
        $route = URL::route($this->routeName, $user);

        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_dispatches_a_new_message_event()
    {
        Event::fake(NewMessage::class);

        $staff = Staff::factory()->createQuietly();
        $user  = User::factory()->create();

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::USER => $user,
            RequestKeys::MESSAGE  => 'This is a test message',
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
        Event::assertDispatched(NewMessage::class);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        Event::fake(NewMessage::class);

        $staff           = Staff::factory()->createQuietly();
        $user            = User::factory()->create();
        $message         = PubnubMessageTypes::TEXT;
        $message['text'] = 'This is a test message';

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::USER => $user,
            RequestKeys::MESSAGE  => $message['text'],
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
    }
}
