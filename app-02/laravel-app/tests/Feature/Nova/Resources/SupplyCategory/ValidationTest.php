<?php

namespace Tests\Feature\Nova\Resources\SupplyCategory;

use App\Models\SupplyCategory as SupplyCategoryModel;
use App\Nova\Resources\SupplyCategory;
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

        $this->path = '/nova-api/' . SupplyCategory::uriKey() . DIRECTORY_SEPARATOR;
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
        $supplyCategory = SupplyCategoryModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Name']),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_creating()
    {
        $name = Str::random(256);

        $response = $this->postJson($this->path, ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_updating()
    {
        $name = Str::random(256);

        $supplyCategory = SupplyCategoryModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_sort_must_be_an_integer_when_creating()
    {
        $sort = 'a string';

        $response = $this->postJson($this->path, ['sort' => $sort]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.integer', ['attribute' => 'Sort']),
        ]);
    }

    /** @test */
    public function its_sort_must_be_greater_than_zero_when_creating()
    {
        $sort = '0';

        $response = $this->postJson($this->path, ['sort' => $sort]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.min.numeric', ['attribute' => 'Sort', 'min' => 1]),
        ]);
    }

    /** @test */
    public function its_sort_must_be_an_integer_when_updating()
    {
        $sort = 'a string';

        $supplyCategory = SupplyCategoryModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), ['sort' => $sort]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.integer', ['attribute' => 'Sort']),
        ]);
    }

    /** @test */
    public function its_sort_must_be_grater_than_zero_when_updating()
    {
        $sort = 0;

        $supplyCategory = SupplyCategoryModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), ['sort' => $sort]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'sort' => Lang::get('validation.min.numeric', ['attribute' => 'Sort', 'min' => 1]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_on_creation()
    {
        $response = $this->postJson($this->path, [
            'name' => 'test value',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_on_update()
    {
        $supplyCategory = SupplyCategoryModel::factory()->create();
        $data           = [
            'name' => 'test name',
            'sort' => 1,
        ];
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), $data);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }
}
