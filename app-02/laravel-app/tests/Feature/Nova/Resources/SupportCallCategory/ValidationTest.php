<?php

namespace Tests\Feature\Nova\Resources\SupportCallCategory;

use App\Models\SupportCallCategory as SupportCallCategoryModel;
use App\Nova\Resources\SupportCallCategory;
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

        $this->path = '/nova-api/' . SupportCallCategory::uriKey() . '/';
    }

    /** @test */
    public function its_name_is_required_when_creating()
    {
        $response = $this->postJson($this->path, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Name']),
        ]);
    }

    /** @test */
    public function its_name_is_required_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Name']),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_creating()
    {
        $response = $this->postJson($this->path, ['name' => Str::random(256)]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), [
            'name' => Str::random(256),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_description_must_have_less_than_256_characters_when_creating()
    {
        $response = $this->postJson($this->path, ['description' => Str::random(256)]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'description' => Lang::get('validation.max.string', ['attribute' => 'Description', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_description_must_have_less_than_256_characters_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), [
            'description' => Str::random(256),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'description' => Lang::get('validation.max.string', ['attribute' => 'Description', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_phone_is_required_when_creating()
    {
        $response = $this->postJson($this->path, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'phone' => Lang::get('validation.required', ['attribute' => 'Phone']),
        ]);
    }

    /** @test */
    public function its_phone_is_required_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'phone' => Lang::get('validation.required', ['attribute' => 'Phone']),
        ]);
    }

    /** @test */
    public function its_phone_must_have_less_than_256_characters_when_creating()
    {
        $response = $this->postJson($this->path, ['phone' => Str::random(256)]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'phone' => Lang::get('validation.max.string', ['attribute' => 'Phone', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_phone_must_have_less_than_256_characters_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), [
            'phone' => Str::random(256),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'phone' => Lang::get('validation.max.string', ['attribute' => 'Phone', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_sort_must_be_an_integer_when_creating()
    {
        $response = $this->postJson($this->path, ['sort' => 'sort']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.integer', ['attribute' => 'Sort']),
        ]);
    }

    /** @test */
    public function its_sort_must_be_greater_than_zero_when_creating()
    {
        $response = $this->postJson($this->path, ['sort' => 0]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.min.numeric', ['attribute' => 'Sort', 'min' => 1]),
        ]);
    }

    /** @test */
    public function its_sort_must_be_an_integer_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), ['sort' => 'sort']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.integer', ['attribute' => 'Sort']),
        ]);
    }

    /** @test */
    public function its_sort_must_be_grater_than_zero_when_updating()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $response            = $this->putJson($this->path . $supportCallCategory->getKey(), ['sort' => 0]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.min.numeric', ['attribute' => 'Sort', 'min' => 1]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_on_creation()
    {
        $response = $this->postJson($this->path, [
            'name'        => 'new name',
            'description' => 'new description',
            'phone'       => 'new phone',
            'sort'        => 2,
        ]);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_on_update()
    {
        $supportCallCategory = SupportCallCategoryModel::factory()->create();
        $data                = [
            'name'        => 'new name',
            'description' => 'new description',
            'phone'       => 'new phone',
            'sort'        => 2,
        ];

        $response = $this->putJson($this->path . $supportCallCategory->getKey(), $data);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }
}
