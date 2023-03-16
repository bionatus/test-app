<?php

namespace Tests\Feature\Api\Nova\JobTitle;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\Nova\JobTitleController;
use App\Http\Requests\Api\Nova\JobTitle\IndexRequest;
use App\Http\Resources\Api\Nova\JobTitle\JobTitleResource;
use App\Types\CompanyDataType;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Nova\Exceptions\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\Nova\TestCase;
use URL;

/** @see JobTitleController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_NOVA_JOB_TITLE_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function an_unauthorized_user_can_not_proceed()
    {
        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        $user->saveQuietly();

        $this->actingAs($user);

        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_list_of_job_titles()
    {
        $this->login();

        $response = $this->get(URL::route($this->routeName, [
            RequestKeys::COMPANY_TYPE => CompanyDataType::TYPE_CONTRACTOR,
        ]));

        $response->assertStatus(Response::HTTP_OK);

        $jobTitles = Collection::make(CompanyDataType::getJobTitles(CompanyDataType::TYPE_CONTRACTOR));

        $this->validateResponseSchema($this->collectionSchema(JobTitleResource::jsonSchema(), false), $response);

        $this->assertCount(count($jobTitles), $response->json());
        $data = Collection::make($response->json());
        $data->each(function(array $rawJobTitle, int $index) use ($jobTitles) {
            $jobTitle = $jobTitles->get($index);
            $this->assertSame($jobTitle, $rawJobTitle['value']);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->wrap = JobTitleResource::$wrap;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        JobTitleResource::wrap($this->wrap);
    }
}
