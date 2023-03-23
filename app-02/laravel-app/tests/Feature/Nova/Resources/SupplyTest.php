<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\RoutePrefixes;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Nova\Resources\Supply as SupplyResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see SupplyResource */
class SupplyTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/' . RoutePrefixes::NOVA_API . '/' . SupplyResource::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_supplies()
    {
        $supplies = Supply::factory()->count(40)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $supplies);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $supplies->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test * */
    public function a_supply_can_be_retrieved_with_correct_resource_elements()
    {
        $supply = Supply::factory()->create();

        $response = $this->getJson($this->path . $supply->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $supply->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $supply->name,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'internal_name',
                'value'     => $supply->internal_name,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'sort',
                'value'     => $supply->sort,
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'visible_at',
                'value'     => $supply->visible_at,
            ],
            [
                'component' => 'belongs-to-field',
                'attribute' => 'supplyCategory',
                'value'     => $supply->supplyCategory->name,
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $supply->name,
            'resource' => [
                'id'     => [
                    'value' => $supply->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_creates_a_supply()
    {
        $supplyCategory = SupplyCategory::factory()->create();

        $fieldsToUpdate = Collection::make([
            'name'           => 'new name',
            'internal_name'  => 'new internal name',
            'sort'           => 2,
            'visible_at'     => null,
            'supplyCategory' => $supplyCategory->getKey(),
        ]);

        $response = $this->postJson($this->path, $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $fieldsToUpdate->put('supply_category_id', $supplyCategory->getKey());
        $fieldsToUpdate->forget('supplyCategory');
        $this->assertDatabaseHas(Supply::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_updates_a_supply()
    {
        $supplyCategory = SupplyCategory::factory()->create();
        $supply         = Supply::factory()->create();

        $fieldsToUpdate = Collection::make([
            'name'           => 'new name',
            'internal_name'  => 'new internal name',
            'sort'           => 2,
            'visible_at'     => null,
            'supplyCategory' => $supplyCategory->getKey(),
        ]);

        $response = $this->putJson($this->path . $supply->getKey(), $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $fieldsToUpdate->put('id', $supply->getKey());
        $fieldsToUpdate->put('supply_category_id', $supplyCategory->getKey());
        $fieldsToUpdate->forget('supplyCategory');
        $this->assertDatabaseHas(Supply::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_destroys_a_supply()
    {
        $supply = Supply::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $supply->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($supply);
    }
}
