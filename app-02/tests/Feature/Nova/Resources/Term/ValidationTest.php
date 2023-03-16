<?php

namespace Tests\Feature\Nova\Resources\Term;

use App\Models\Term;
use App\Nova\Resources\Term as TermResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . TermResource::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function its_title_is_required_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'title' => Lang::get('validation.required', ['attribute' => 'Title']),
        ]);
    }

    /** @test */
    public function its_title_must_have_less_than_27_characters_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, ['title' => Str::random(27)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'title' => Lang::get('validation.max.string', ['attribute' => 'Title', 'max' => 26]),
        ]);
    }

    /** @test */
    public function its_body_is_required_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'body' => Lang::get('validation.required', ['attribute' => 'Body']),
        ]);
    }

    /** @test */
    public function its_required_at_is_required_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'required_at' => Lang::get('validation.required', ['attribute' => 'Required At']),
        ]);
    }

    /** @test */
    public function its_link_is_required_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.required', ['attribute' => 'Link']),
        ]);
    }

    /** @test */
    public function its_link_must_have_less_than_256_characters_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, ['link' => Str::random(256)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.max.string', ['attribute' => 'Link', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_link_must_be_a_url_when_creating()
    {
        $note     = Term::factory()->create();
        $response = $this->postJson($this->path, ['link' => 'invalid-url']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.url', ['attribute' => 'Link']),
        ]);
    }

    /** @test */
    public function its_title_is_required_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'title' => Lang::get('validation.required', ['attribute' => 'Title']),
        ]);
    }

    /** @test */
    public function its_title_must_have_less_than_27_characters_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['title' => Str::random(27)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'title' => Lang::get('validation.max.string', ['attribute' => 'Title', 'max' => 26]),
        ]);
    }

    /** @test */
    public function its_body_is_required_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'body' => Lang::get('validation.required', ['attribute' => 'Body']),
        ]);
    }

    /** @test */
    public function its_required_at_is_required_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'required_at' => Lang::get('validation.required', ['attribute' => 'Required At']),
        ]);
    }

    /** @test */
    public function its_link_is_required_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.required', ['attribute' => 'Link']),
        ]);
    }

    /** @test */
    public function its_link_must_have_less_than_256_characters_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['link' => Str::random(256)]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.max.string', ['attribute' => 'Link', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_link_must_be_a_url_when_updating()
    {
        $note     = Term::factory()->create();
        $response = $this->putJson($this->path . $note->getKey(), ['link' => 'invalid-url']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'link' => Lang::get('validation.url', ['attribute' => 'Link']),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_when_updating()
    {
        $response = $this->postJson($this->path, [
            'title'       => 'A title',
            'body'        => 'a body',
            'link'        => 'http://link.test',
            'required_at' => '2022-27-09 00:00:00',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $noteId    = $response->json('id');
        $response2 = $this->putJson($this->path . $noteId, [
            'title'       => 'A title',
            'body'        => 'a body',
            'link'        => 'http://link.test',
            'required_at' => '2022-27-09 00:00:00',
        ]);
        $response2->assertJsonMissingValidationErrors();
        $response2->assertStatus(Response::HTTP_OK);
    }
}
