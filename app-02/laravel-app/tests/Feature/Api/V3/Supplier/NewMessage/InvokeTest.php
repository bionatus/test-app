<?php

namespace Tests\Feature\Api\V3\Supplier\NewMessage;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Supplier\NewMessage;
use App\Http\Controllers\Api\V3\Supplier\NewMessageController;
use App\Http\Requests\Api\V3\Supplier\NewMessage\InvokeRequest;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PubNub\PubNub;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see NewMessageController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPLIER_NEW_MESSAGE;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $route    = URL::route($this->routeName, $supplier);

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

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::SUPPLIER => $supplier,
            RequestKeys::MESSAGE      => 'This is a test message',
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
        Event::assertDispatched(NewMessage::class);
    }

    /** @test */
    public function it_updates_the_user_last_message_at_field_in_pubnub_channels_table()
    {
        Event::fake(NewMessage::class);

        $pubnubChannel = PubnubChannel::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();

        $this->login($pubnubChannel->user);

        $this->assertNull($pubnubChannel->user_last_message_at);
        $this->post(URL::route($this->routeName, [
            RouteParameters::SUPPLIER => $pubnubChannel->supplier,
            RequestKeys::MESSAGE      => 'This is a test message',
        ]));

        $this->assertNotNull($pubnubChannel->fresh()->user_last_message_at);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        Event::fake(NewMessage::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $message['type'] = 'text';
        $message['text'] = 'This is a test message';

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $this->login($user);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::SUPPLIER => $supplier,
            RequestKeys::MESSAGE      => 'This is a test message',
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
    }
}
