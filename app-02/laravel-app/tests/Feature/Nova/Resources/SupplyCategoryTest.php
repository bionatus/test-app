<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\RoutePrefixes;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Nova\Resources\SupplyCategory as SupplyCategoryResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see SupplyCategoryResource */
class SupplyCategoryTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/' . RoutePrefixes::NOVA_API . '/' . SupplyCategoryResource::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_supply_categories()
    {
        $supplyCategories = SupplyCategory::factory()->count(40)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $supplyCategories);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $supplyCategories->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function a_supply_can_be_retrieved_with_correct_resource_elements($withChildren)
    {
        $parent         = SupplyCategory::factory()->create(['name' => $parentName = 'parent name']);
        $supplyCategory = SupplyCategory::factory()
            ->sort($sort = 2)
            ->usingParent($parent)
            ->create(['name' => $name = 'name']);
        $childrenCount  = 0;
        $suppliesCount  = 0;

        if ($withChildren) {
            SupplyCategory::factory()->usingParent($supplyCategory)->count($childrenCount = 2)->create();
        } else {
            Supply::factory()->usingSupplyCategory($supplyCategory)->count($suppliesCount = 3)->create();
        }

        $response = $this->getJson($this->path . $supplyCategory->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $supplyCategory->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $name,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'sort',
                'value'     => (string) $sort,
                'type'      => 'number',
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'visible_at',
                'value'     => $supplyCategory->visible_at,
            ],
            [
                'component' => 'belongs-to-field',
                'attribute' => 'parent',
                'value'     => $parentName,
            ],
            [
                'component' => 'text-field',
                'indexName' => 'Children Count',
                'attribute' => 'ComputedField',
                'value'     => $childrenCount,
            ],
            [
                'component' => 'text-field',
                'indexName' => 'Supplies Count',
                'attribute' => 'ComputedField',
                'value'     => $suppliesCount,
            ],
            [
                'component' => 'advanced-media-library-field',
                'attribute' => MediaCollectionNames::IMAGES,
                'name'      => 'Image',
                'type'      => 'media',
            ],
            [
                'component'    => 'has-many-field',
                'indexName'    => $withChildren ? 'Children' : 'Supplies',
                'resourceName' => $withChildren ? 'supply-categories' : 'supplies',
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $supplyCategory->name,
            'resource' => [
                'id'     => [
                    'value' => $supplyCategory->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    public function dataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /** @test */
    public function it_creates_a_supply_category()
    {
        $fieldsToUpdate = Collection::make([
            'name' => 'new name',
            'sort' => 2,
            'visible_at' => null,
        ]);

        $response = $this->postJson($this->path, $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplyCategory::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_updates_a_supply_category()
    {
        $supplyCategory = SupplyCategory::factory()->create();

        $fieldsToUpdate = Collection::make([
            'name' => 'new name',
            'sort' => 2,
            'visible_at' => null,
        ]);

        $response = $this->putJson($this->path . $supplyCategory->getKey(), $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $fieldsToUpdate->put('id', $supplyCategory->getKey());
        $this->assertDatabaseHas(SupplyCategory::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_destroys_a_supply_category_without_supplies_and_without_children()
    {
        $supplyCategory = SupplyCategory::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $supplyCategory->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($supplyCategory);
    }
}
