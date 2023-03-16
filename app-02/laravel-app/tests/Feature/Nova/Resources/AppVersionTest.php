<?php

namespace Tests\Feature\Nova\Resources;

use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\AppVersion */
class AppVersionTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/nova-api/' . \App\Nova\Resources\AppVersion::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_app_versions()
    {
        $appVersions = AppVersion::factory()->count(1)->create();
        $response    = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount($response->json('total'), $appVersions);

        $data = Collection::make($response->json('resources'));

        $firstPageAppSettings = $appVersions->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageAppSettings->pluck('id'));
    }

    /** @test */
    public function it_does_not_allow_creation_of_new_version()
    {
        $versionFields = Collection::make([
            'version'     => '1.2.3',
            'video_title' => 'title',
            'video_url'   => 'url',
            'message'     => 'a simple message',
        ]);

        $response = $this->postJson($this->path, $versionFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test * */
    public function an_app_version_can_be_retrieved_with_correct_resource_elements()
    {
        $appVersion = AppVersion::factory()->create();

        $response = $this->getJson($this->path . $appVersion->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'text-field',
                'attribute' => 'min',
                'value'     => $appVersion->min,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'current',
                'value'     => $appVersion->current,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'video_title',
                'value'     => $appVersion->video_title,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'video_url',
                'value'     => $appVersion->video_url,
            ],
            [
                'component' => 'textarea-field',
                'attribute' => 'message',
                'value'     => htmlentities($appVersion->message),
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $appVersion->current,
            'resource' => [
                'id'     => [
                    'value' => $appVersion->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_the_app_version()
    {
        $appVersion       = AppVersion::factory()->create();
        $appVersionFields = Collection::make([
            'min'         => $min = '5.5.5',
            'current'     => $current = '7.0.0',
            'video_title' => $title = 'title',
            'video_url'   => $url = 'http://www.url.com',
            'message'     => $message = 'test message',
        ]);

        $response = $this->putJson($this->path . $appVersion->getKey(), $appVersionFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(AppVersion::tableName(), [
            'id'          => $appVersion->getKey(),
            'min'         => $min,
            'current'     => $current,
            'video_title' => $title,
            'video_url'   => $url,
            'message'     => $message,
        ]);
    }

    /** @test */
    public function it_does_not_destroy_the_app_version()
    {
        $appVersion = AppVersion::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $appVersion->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelExists($appVersion);
    }
}
