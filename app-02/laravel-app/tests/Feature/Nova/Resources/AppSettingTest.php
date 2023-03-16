<?php

namespace Tests\Feature\Nova\Resources;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\AppSetting */
class AppSettingTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/nova-api/' . \App\Nova\Resources\AppSetting::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_app_settings()
    {
        $appSettings = AppSetting::factory()->count(10)->create();
        $response    = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount($response->json('total'), $appSettings);

        $data = Collection::make($response->json('resources'));

        $firstPageAppSettings = $appSettings->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageAppSettings->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_do_not_allow_creation()
    {
        $plainTagFields = Collection::make([
            'label' => 'AppSetting Label',
            'value' => 'app setting value',
            'type'  => 'string',
        ]);

        $response = $this->postJson($this->path, $plainTagFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test * */
    public function an_app_setting_can_be_retrieved_with_correct_resource_elements()
    {
        $appSetting = AppSetting::factory()->create();

        $response = $this->getJson($this->path . $appSetting->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $appSetting->getKey(),
                'name'      => 'ID',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'label',
                'value'     => $appSetting->label,
                'name'      => 'Label',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'value_display',
                'value'     => $appSetting->value,
                'name'      => 'Value',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'type',
                'value'     => $appSetting->type,
                'name'      => 'Type',
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $appSetting->label,
            'resource' => [
                'id'     => [
                    'value' => $appSetting->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_an_app_setting()
    {
        $appSetting       = AppSetting::factory()->create();
        $appSettingFields = Collection::make([
            'value' => $value = 'Value edited',
        ]);

        $response = $this->putJson($this->path . $appSetting->getKey(), $appSettingFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(AppSetting::tableName(), [
            'id'    => $appSetting->getKey(),
            'label' => $appSetting->label,
            'value' => $value,
            'type'  => $appSetting->type,
        ]);
    }

    /** @test */
    public function it_does_not_destroy_an_app_setting()
    {
        $appSetting = AppSetting::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $appSetting->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelExists($appSetting);
    }
}
