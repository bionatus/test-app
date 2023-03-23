<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\RoutePrefixes;
use App\Models\InstrumentSupportCallCategory;
use App\Models\SupportCallCategory;
use App\Nova\Resources\SupportCallCategory as SupportCallCategoryResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see SupportCallCategoryResource */
class SupportCallCategoryTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/' . RoutePrefixes::NOVA_API . '/' . SupportCallCategoryResource::uriKey() . '/';
    }

    /** @test */
    public function it_displays_a_list_of_support_call_categories()
    {
        $supportCallCategories = SupportCallCategory::factory()->count(40)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $supportCallCategories);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $supportCallCategories->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function a_support_call_category_can_be_retrieved_with_correct_resource_elements(
        $withParent,
        $withChildren,
        $withInstruments
    ) {
        if ($withParent) {
            $parent              = SupportCallCategory::factory()->create(['name' => $parentName = 'parent name']);
            $supportCallCategory = SupportCallCategory::factory()->sort($sort = 2)->usingParent($parent);
        } else {
            $supportCallCategory = SupportCallCategory::factory()->sort($sort = 2);
        }

        $supportCallCategory = $supportCallCategory->create([
            'name'        => $name = 'name',
            'description' => $description = 'description',
            'phone'       => $phone = 'phone',
        ]);

        if ($withChildren) {
            SupportCallCategory::factory()->usingParent($supportCallCategory)->count(2)->create();
        }

        if ($withInstruments) {
            InstrumentSupportCallCategory::factory()
                ->usingSupportCallCategory($supportCallCategory)
                ->count(2)
                ->create();
        }

        $response = $this->getJson($this->path . $supportCallCategory->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'attribute' => 'id',
                'component' => 'id-field',
                'value'     => $supportCallCategory->getKey(),
            ],
            [
                'attribute' => 'name',
                'component' => 'text-field',
                'value'     => $name,
            ],
            [
                'attribute' => 'description',
                'component' => 'text-field',
                'value'     => $description,
            ],
            [
                'attribute' => 'phone',
                'component' => 'text-field',
                'value'     => $phone,
            ],
            [
                'attribute' => 'sort',
                'component' => 'text-field',
                'value'     => (string) $sort,
                'type'      => 'number',
            ],
            [
                'attribute' => 'parent',
                'component' => 'belongs-to-field',
                'value'     => $parentName ?? null,
            ],
            [
                'attribute' => MediaCollectionNames::IMAGES,
                'component' => 'advanced-media-library-field',
                'name'      => 'Image',
                'type'      => 'media',
            ],
        ];

        if (!$withParent || $withChildren) {
            $fields[] = [
                'component'    => 'has-many-field',
                'resourceName' => 'support-call-categories',
                'indexName'    => 'Children',
            ];
        }

        if (!$withChildren || $withInstruments) {
            $fields[] = [
                'component'    => 'belongs-to-many-field',
                'resourceName' => 'instruments',
                'indexName'    => 'Instruments',
            ];
        }

        $this->assertCount(count($fields), $response->json('resource.fields'));
        $response->assertJson([
            'title'    => $supportCallCategory->name,
            'resource' => [
                'id'     => [
                    'value' => $supportCallCategory->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    public function dataProvider(): array
    {
        return [
            //$withParent, $withChildren, $withInstruments
            [true, true, true],
            [true, true, false],
            [true, false, true],
            [true, false, false],
            [false, true, true],
            [false, true, false],
            [false, false, true],
            [false, false, false],
        ];
    }

    /** @test */
    public function it_creates_a_support_call_category()
    {
        $fieldsToUpdate = Collection::make([
            'name'        => 'new name',
            'description' => 'new description',
            'phone'       => 'new phone',
            'sort'        => 2,
        ]);

        $response = $this->postJson($this->path, $fieldsToUpdate->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(SupportCallCategory::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_updates_a_support_call_category()
    {
        $supportCallCategory = SupportCallCategory::factory()->create();

        $fieldsToUpdate = Collection::make([
            'name'        => 'new name',
            'description' => 'new description',
            'phone'       => 'new phone',
            'sort'        => 2,
        ]);

        $response = $this->putJson($this->path . $supportCallCategory->getKey(), $fieldsToUpdate->toArray());
        $fieldsToUpdate->put('id', $supportCallCategory->getKey());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas(SupportCallCategory::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_destroys_a_support_call_category_without_children()
    {
        $supportCallCategory = SupportCallCategory::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $supportCallCategory->getKey());

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelMissing($supportCallCategory);
    }

    /** @test */
    public function it_does_not_destroys_a_support_call_category_with_children()
    {
        $supportCallCategory = SupportCallCategory::factory()->create();
        SupportCallCategory::factory()->usingParent($supportCallCategory)->count(3)->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $supportCallCategory->getKey());

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelExists($supportCallCategory);
    }
}
