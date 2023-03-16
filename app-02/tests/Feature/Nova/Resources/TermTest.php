<?php

namespace Tests\Feature\Nova\Resources;

use App;
use App\Constants\RoutePrefixes;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\Term */
class TermTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = DIRECTORY_SEPARATOR . RoutePrefixes::NOVA_API . DIRECTORY_SEPARATOR . App\Nova\Resources\Term::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_terms()
    {
        $terms    = Term::factory()->count(20)->create();
        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $terms);

        $data = Collection::make($response->json('resources'));

        $firstPageTerms = $terms->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageTerms->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test * */
    public function a_term_can_be_retrieved_with_correct_resource_elements()
    {
        $term = Term::factory()->create();

        $response = $this->getJson($this->path . $term->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $term->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'title',
                'value'     => $term->title,
            ],
            [
                'component' => 'textarea-field',
                'attribute' => 'body',
                'value'     => $term->body,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'link',
                'value'     => $term->link,
            ],
            [
                'component' => 'date',
                'attribute' => 'required_at',
                'value'     => $term->required_at->toDateString(),
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $term->title,
            'resource' => [
                'id'     => [
                    'value' => $term->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_creates_a_term()
    {
        $fieldsToCreate = Collection::make([
            'title'       => 'new Title',
            'body'        => 'Test Body',
            'link'        => 'https://www.termsandconditions.com/1',
            'required_at' => '2022-09-27 00:00:00',
        ]);

        $response = $this->postJson($this->path, $fieldsToCreate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(Term::tableName(), $fieldsToCreate->toArray());
    }

    /** @test */
    public function it_updates_a_term()
    {
        $term = Term::factory()->create();

        $fieldsToUpdate = Collection::make([
            'title'       => 'Updated Title',
            'body'        => 'Test Body Updated',
            'link'        => 'https://www.termsandconditions.com/1',
            'required_at' => '2022-09-27 00:00:00',
        ]);

        $response = $this->putJson($this->path . $term->getKey(), $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $fieldsToUpdate->put('id', $term->getKey());
        $this->assertDatabaseHas(Term::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_destroys_a_term()
    {
        $term = Term::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $term->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($term);
    }
}
