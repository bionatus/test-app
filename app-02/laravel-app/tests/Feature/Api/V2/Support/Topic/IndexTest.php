<?php

namespace Tests\Feature\Api\V2\Support\Topic;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\Support\TopicController;
use App\Http\Resources\Api\V2\Support\Topic\BaseResource;
use App\Models\Media;
use App\Models\SubjectTool;
use App\Models\Subtopic;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see TopicController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;

    private string $routeName = RouteNames::API_V2_SUPPORT_TOPIC_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_display_a_list_of_topics()
    {
        $topics = Topic::factory()->count(2)->create();
        $topic  = $topics->first();
        SubjectTool::factory()->usingSubject($topic->subject)->count(2)->create();
        Media::factory()->usingSubject($topic->subject)->count(2)->create();
        $subtopics = Subtopic::factory()->usingTopic($topic)->count(2)->create();

        $subtopic = $subtopics->first();
        Media::factory()->usingSubject($subtopic->subject)->count(3)->create();
        SubjectTool::factory()->usingSubject($subtopic->subject)->count(3)->create();

        $route = URL::route($this->routeName);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $topics);

        $data            = Collection::make($response->json('data'));
        $firstPageTopics = $topics->values()->take(count($data));

        $data->each(function (array $rawTopic, int $index) use ($firstPageTopics) {
            $topic = $firstPageTopics->get($index);
            $this->assertSame($topic->subject->getRouteKey(), $rawTopic['id']);
        });
    }
}
