<?php

namespace Tests\Feature\Api\V3\Supplier\ChangeRequest;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\SupplierChangeRequestReasons;
use App\Http\Controllers\Api\V3\Supplier\ChangeRequestController;
use App\Http\Requests\Api\V3\Supplier\ChangeRequest\InvokeRequest;
use App\Mail\Supplier\ChangeRequestEmail;
use App\Models\Supplier;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see ChangeRequestController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPLIER_CHANGE_REQUEST;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $store = Supplier::factory()->createQuietly();
        $route = URL::route($this->routeName, $store);

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
    public function it_sends_a_supplier_change_request_email_to_bluon()
    {
        Mail::fake();

        Config::set('mail.support.supplier.change', [$email = 'jon@doe.com']);

        $supplier = Supplier::factory()->createQuietly();
        $route    = URL::route($this->routeName, $supplier);

        $this->login();
        $response = $this->post($route, [
            RequestKeys::REASON => SupplierChangeRequestReasons::REASON_INCORRECT_WRONG,
            RequestKeys::DETAIL => 'a detail',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Mail::assertQueued(ChangeRequestEmail::class, function($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }
}
