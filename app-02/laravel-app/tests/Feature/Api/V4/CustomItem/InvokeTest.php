<?php

namespace Tests\Feature\Api\V4\CustomItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\CustomItemController;
use App\Http\Requests\Api\V4\CustomItem\InvokeRequest;
use App\Http\Resources\Api\V4\CustomItem\BaseResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CustomItemController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_CUSTOM_ITEM_STORE;

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
    public function it_creates_a_custom_item_with_a_related_item()
    {
        $user  = User::factory()->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::NAME => $expectedName = 'fake custom item',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = $response->json('data');
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(CustomItem::tableName(), [
            'name'         => $expectedName,
            'creator_id'   => $user->getKey(),
            'creator_type' => User::MORPH_ALIAS,
        ]);

        $this->assertDatabaseHas(Item::tableName(), [
            'uuid' => $data['id'],
            'type' => Item::TYPE_CUSTOM_ITEM,
        ]);
    }
}
