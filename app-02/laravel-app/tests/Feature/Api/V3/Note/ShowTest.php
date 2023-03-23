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
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_NOTE_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $noteCategory = NoteCategory::factory()->create();
        $note         = Note::factory()->usingNoteCategory($noteCategory)->create();
        $route        = URL::route($this->routeName, [
            RouteParameters::NOTE_CATEGORY => $noteCategory->getRouteKey(),
            RouteParameters::NOTE          => $note->getRouteKey(),
        ]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_displays_a_note()
    {
        $noteCategory = NoteCategory::factory()->create();
        $note         = Note::factory()->usingNoteCategory($noteCategory)->create();
        $route        = URL::route($this->routeName, [
            RouteParameters::NOTE_CATEGORY => $noteCategory->getRouteKey(),
            RouteParameters::NOTE          => $note->getRouteKey(),
        ]);

        $this->login(User::factory()->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $note->getRouteKey());
    }
}
