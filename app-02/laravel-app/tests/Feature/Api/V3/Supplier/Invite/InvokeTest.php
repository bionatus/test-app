<?php

namespace Tests\Feature\Api\V3\Supplier\Invite;

use App\Constants\RouteNames;
use App\Mail\Supplier\InviteEmail;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see InviteController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPLIER_INVITE;

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
    public function it_sends_an_invitation_to_bluon_to_the_supplier()
    {
        Mail::fake();

        $supplier = Supplier::factory()->createQuietly(['email' => 'jon@doe.com']);
        $route    = URL::route($this->routeName, $supplier);

        $this->login();
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        Mail::assertQueued(InviteEmail::class, function($mail) use ($supplier) {
            return $mail->hasTo($supplier->email);
        });
    }

    /** @test */
    public function it_must_register_that_the_user_has_already_invited_the_supplier()
    {
        Mail::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['email' => 'jon@doe.com']);
        $route    = URL::route($this->routeName, $supplier);

        $this->login($user);
        $this->post($route);

        $this->assertDatabaseHas(SupplierInvitation::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'user_id'     => $user->getKey(),
        ]);
    }

    /** @test */
    public function it_should_not_send_the_invitation_more_than_once_per_supplier()
    {
        $supplierInvitation = SupplierInvitation::factory()->createQuietly();
        $user               = $supplierInvitation->user;
        $supplier           = $supplierInvitation->supplier;
        $route              = URL::route($this->routeName, $supplier);

        $this->login($user);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
