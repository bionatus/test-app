<?php

namespace Tests\Feature\Api\V3\Note;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\NoteController;
use App\Http\Resources\Api\V3\Note\BaseResource;
use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see NoteController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_NOTE_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $noteCategory = NoteCategory::factory()->create();

        $this->get(URL::route($this->routeName, [RouteParameters::NOTE_CATEGORY => $noteCategory->getRouteKey()]));
    }

    /** @test */
    public function it_displays_a_list_of_notes_according_to_a_category_sorted_by_sort_column()
    {
        $user = User::factory()->create();

        $noteCategory = NoteCategory::factory()->create();
        $noteOne      = Note::factory()->usingNoteCategory($noteCategory)->create(['sort' => 1]);
        $noteThree    = Note::factory()->usingNoteCategory($noteCategory)->create(['sort' => 3]);
        $noteTwo      = Note::factory()->usingNoteCategory($noteCategory)->create(['sort' => 2]);

        Note::factory()->count(2)->create();

        $notes = Collection::make([$noteOne, $noteTwo, $noteThree]);

        $notesRouteKeys = $notes->pluck(Note::routeKeyName())->toArray();

        $this->login($user);
        $route         = URL::route($this->routeName, [RouteParameters::NOTE_CATEGORY => $noteCategory->getRouteKey()]);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Note::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $notes);
        $this->assertEquals($notesRouteKeys, $dataRouteKeys);
    }
}
