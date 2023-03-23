<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\RoutePrefixes;
use App\Models\NoteCategory;
use App\Nova\Resources\NoteCategory as NoteCategoryResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see NoteCategoryResource */
class NoteCategoryTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/' . RoutePrefixes::NOVA_API . '/' . NoteCategoryResource::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_note_categories()
    {
        $noteCategories = NoteCategory::factory()->count(40)->create();
        $response       = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $noteCategories);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $noteCategories->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test * */
    public function a_note_category_can_be_retrieved_with_correct_resource_elements()
    {
        $noteCategory = NoteCategory::factory()->create();

        $response = $this->getJson($this->path . $noteCategory->getKey());
        $response->assertStatus(Response::HTTP_OK);
        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $noteCategory->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'slug',
                'value'     => $noteCategory->getRouteKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $noteCategory->name,
            ],
            [
                'component' => 'has-many-field',
                'attribute' => 'notes',
                'value'     => null,
            ],
        ];
        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $noteCategory->name,
            'resource' => [
                'id'     => [
                    'value' => $noteCategory->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }
}
