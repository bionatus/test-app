<?php

namespace Tests\Feature\Api\V3\ModelType;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V3\ModelType\BaseResource;
use App\Models\ModelType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ModelTypesController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_MODEL_TYPE_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_model_types_sorted_by_sort_field_with_null_at_last()
    {
        $modelTypesWithoutSort = ModelType::factory()->count(3)->create();
        $secondModelType       = ModelType::factory()->create(['sort' => 2]);
        $firstModelType        = ModelType::factory()->create(['sort' => 1]);
        $expected              = $modelTypesWithoutSort->prepend($secondModelType)->prepend($firstModelType);

        $route = URL::route($this->routeName);
        $this->login();

        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $data->each(function(array $rawModelType) use ($expected) {
            $modelType = $expected->shift();
            $this->assertSame($modelType->getRouteKey(), $rawModelType['id']);
        });
    }
}
