<?php

namespace Tests\Feature\LiveApi\V1\Auth\Email\InitialPassword;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Auth\Email\InitialPasswordController;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see InitialPasswordController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_AUTH_EMAIL_INITIAL_PASSWORD;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->markTestSkipped();
        //$this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_should_set_the_initial_password()
    {
        Auth::shouldUse('live');
        $staff       = Staff::factory()->createQuietly(['initial_password_set_at' => null]);
        $oldPassword = $staff->password;
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::PASSWORD              => 'password',
            RequestKeys::PASSWORD_CONFIRMATION => 'password',
            RequestKeys::TOS_ACCEPTED          => true,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNotNull($staff->fresh()->initial_password_set_at);
        $this->assertNotEquals($oldPassword, $staff->fresh()->password);
    }

    /** @test */
    public function it_should_mark_the_user_has_accepted_the_tos()
    {
        Auth::shouldUse('live');
        $staff = Staff::factory()->createQuietly(['initial_password_set_at' => null]);
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::PASSWORD              => 'password',
            RequestKeys::PASSWORD_CONFIRMATION => 'password',
            RequestKeys::TOS_ACCEPTED          => true,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNotNull($staff->refresh()->tos_accepted_at);
    }
}
