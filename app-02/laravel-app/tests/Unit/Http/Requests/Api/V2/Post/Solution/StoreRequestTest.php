<?php

namespace Tests\Unit\Http\Requests\Api\V2\Post\Solution;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\Post\SolutionController;
use App\Http\Requests\Api\V2\Post\Solution\StoreRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Route;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SolutionController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected string $requestClass = StoreRequest::class;
    protected Post   $post;

    protected function setUp(): void
    {
        parent::setUp();

        Route::model('post', Post::class);
        $this->post = Post::factory()->create();
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::POST, $this->post->getRouteKey())
            ->assertAuthorized();
    }

    /** @test */
    public function it_requires_a_solution()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::POST, $this->post->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOLUTION]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::SOLUTION])]);
    }

    /** @test */
    public function solution_must_be_an_uuid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SOLUTION => 'just a string'])
            ->addRouteParameter(RouteParameters::POST, $this->post->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOLUTION]);
        $request->assertValidationMessages([Lang::get('validation.uuid', ['attribute' => RequestKeys::SOLUTION])]);
    }

    /** @test */
    public function the_solution_must_exist_in_comments_table()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SOLUTION => $this->faker->uuid])
            ->addRouteParameter(RouteParameters::POST, $this->post->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOLUTION]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::SOLUTION])]);
    }

    /** @test */
    public function the_solution_must_be_a_comment_of_the_route_post()
    {
        $comment = Comment::factory()->create();
        $request = $this->formRequest($this->requestClass, [RequestKeys::SOLUTION => $comment->getRouteKey()])
            ->addRouteParameter(RouteParameters::POST, $this->post->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOLUTION]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::SOLUTION])]);
    }
}
