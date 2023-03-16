<?php

namespace Tests\Feature\BasecampApi\V1\User\Supplier;

use App\Constants\RouteNames;
use App\Http\Controllers\BasecampApi\V1\User\SupplierController;
use App\Http\Resources\BasecampApi\V1\User\Supplier\BaseResource;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see SupplierController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::BASECAMP_API_V1_USER_SUPPLIER_INDEX;

    /** @test */
    public function it_returns_a_list_of_user_suppliers()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()
            ->has(SupplierUser::factory()->usingUser($user)->confirmed())
            ->count(5)
            ->createQuietly();
        Supplier::factory()->count(3)->createQuietly();
        $route = URL::route($this->routeName, [$user]);

        Config::set('basecamp.token.key', $key = 'test_key');
        $token    = Hash::make($key);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $suppliers);
    }
}
