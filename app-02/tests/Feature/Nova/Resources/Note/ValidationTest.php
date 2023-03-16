<?php

namespace Tests\Feature\Nova\Resources\Note;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Nova\Resources\Note as NoteResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see NoteResource */
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . NoteResource::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function its_title_is_required_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'title' => Lang::get('validation.required', ['attribute' => 'Title']),
        ]);
    }

    /** @test */
    public function its_title_must_have_less_than_27_characters_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['title' => Str::random(27)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'title' => Lang::get('validation.max.string', ['attribute' => 'Title', 'max' => 26]),
        ]);
    }

    /** @test */
    public function its_body_is_required_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'body' => Lang::get('validation.required', ['attribute' => 'Body']),
        ]);
    }

    /** @test */
    public function its_body_must_have_less_than_91_characters_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['body' => Str::random(91)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'body' => Lang::get('validation.max.string', ['attribute' => 'Body', 'max' => 90]),
        ]);
    }

    /** @test */
    public function its_link_must_have_less_than_256_characters_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['link' => Str::random(256)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.max.string', ['attribute' => 'Link', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_link_must_be_a_url_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['link' => 'invalid-url']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.url', ['attribute' => 'Link']),
        ]);
    }

    /** @test */
    public function its_link_text_must_have_less_than_256_characters_when_updating()
    {
        $note     = Note::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['link_text' => Str::random(256)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link_text' => Lang::get('validation.max.string', ['attribute' => 'Link Text', 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_when_updating()
    {
        $noteCategory = NoteCategory::factory()->featured()->create();
        $note         = Note::factory()->usingNoteCategory($noteCategory)->create();
        $response     = $this->putJson($this->path . $note->getKey(), [
            'title'        => 'A title',
            'body'         => 'a body',
            'link'         => 'http://link.test',
            'link_text'    => 'a link text',
            'sort'         => rand(1, 5),
            'noteCategory' => $noteCategory->getKey(),
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }
}
