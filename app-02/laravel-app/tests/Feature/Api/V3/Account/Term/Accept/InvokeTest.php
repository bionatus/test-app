<?php

namespace Tests\Feature\Api\V3\Account\Term\Accept;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Requests\Api\V3\Account\Term\Accept\InvokeRequest;
use App\Http\Resources\Api\V3\Account\Term\BaseResource;
use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see AcceptController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_TERM_ACCEPT;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        Term::factory()->create();

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_register_that_user_has_accepted_a_specific_term()
    {
        $user = User::factory()->create();
        Term::factory()->count(10)->create();
        $currentTerm = Term::factory()->create([
            'title'       => $objectiveTitle = 'Objective Title',
            'required_at' => Carbon::now(),
        ]);

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [RequestKeys::TOS_ACCEPTED => 1]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($objectiveTitle, $data->get('title'));

        $this->assertDatabaseHas(TermUser::tableName(), [
            'user_id' => $user->getKey(),
            'term_id' => $currentTerm->getKey(),
        ]);
    }
}
