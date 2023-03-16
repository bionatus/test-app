<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\RoutePrefixes;
use App\Models\Instrument;
use App\Models\InstrumentSupportCallCategory;
use App\Nova\Resources\Instrument as InstrumentResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see InstrumentResource */
class InstrumentTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/' . RoutePrefixes::NOVA_API . '/' . InstrumentResource::uriKey() . '/';
    }

    /** @test */
    public function it_displays_a_list_of_instruments()
    {
        $instruments = Instrument::factory()->count(40)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $instruments);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $instruments->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function an_instrument_can_be_retrieved_with_correct_resource_elements()
    {
        $instrument = Instrument::factory()->create(['name' => $name = 'name']);

        $response = $this->getJson($this->path . $instrument->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'attribute' => 'id',
                'component' => 'id-field',
                'value'     => $instrument->getKey(),
            ],
            [
                'attribute' => 'name',
                'component' => 'text-field',
                'value'     => $name,
            ],
            [
                'attribute' => MediaCollectionNames::IMAGES,
                'component' => 'advanced-media-library-field',
                'name'      => 'Image',
                'type'      => 'media',
            ],
            [
                'component'    => 'belongs-to-many-field',
                'resourceName' => 'support-call-categories',
                'indexName'    => 'Support Call Categories',
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));
        $response->assertJson([
            'title'    => $instrument->name,
            'resource' => [
                'id'     => [
                    'value' => $instrument->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_creates_an_instrument()
    {
        $fieldsToUpdate = Collection::make([
            'name' => 'new name',
        ]);

        $response = $this->postJson($this->path, $fieldsToUpdate->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(Instrument::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_updates_an_instrument()
    {
        $instrument = Instrument::factory()->create();

        $fieldsToUpdate = Collection::make([
            'name' => 'new name',
        ]);

        $response = $this->putJson($this->path . $instrument->getKey(), $fieldsToUpdate->toArray());
        $fieldsToUpdate->put('id', $instrument->getKey());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas(Instrument::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_destroys_an_instrument_without_support_call_categories()
    {
        $instrument = Instrument::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $instrument->getKey());

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelMissing($instrument);
    }

    /** @test */
    public function it_does_not_destroy_an_instrument_with_support_call_categories()
    {
        $instrument = Instrument::factory()->create();
        InstrumentSupportCallCategory::factory()->usingInstrument($instrument)->count(3)->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $instrument->getKey());

        $response->assertStatus(Response::HTTP_OK);
        $this->assertModelExists($instrument);
    }
}
