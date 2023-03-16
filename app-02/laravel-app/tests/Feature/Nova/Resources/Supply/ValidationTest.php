<?php

namespace Tests\Feature\Nova\Resources\Supply;

use App\Models\Supply as SupplyModel;
use App\Models\SupplyCategory;
use App\Nova\Resources\Supply;
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

        $this->path = '/nova-api/' . Supply::uriKey() . DIRECTORY_SEPARATOR;
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
        $supplyCategory = SupplyModel::factory()->create();
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

        $supplyCategory = SupplyModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_internal_name_is_required_when_creating()
    {
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'internal_name' => Lang::get('validation.required', ['attribute' => 'Name for supplier']),
        ]);
    }

    /** @test */
    public function its_internal_name_is_required_when_updating()
    {
        $supplyCategory = SupplyModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'internal_name' => Lang::get('validation.required', ['attribute' => 'Name for supplier']),
        ]);
    }

    /** @test */
    public function its_internal_name_must_have_less_than_256_characters_when_creating()
    {
        $name = Str::random(256);

        $response = $this->postJson($this->path, ['internal_name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'internal_name' => Lang::get('validation.max.string', ['attribute' => 'Name for supplier', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_internal_name_must_have_less_than_256_characters_when_updating()
    {
        $name = Str::random(256);

        $supplyCategory = SupplyModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), ['internal_name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'internal_name' => Lang::get('validation.max.string', ['attribute' => 'Name for supplier', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_supply_category_is_required_when_creating()
    {
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'supplyCategory' => Lang::get('validation.required', ['attribute' => 'Category']),
        ]);
    }

    /** @test */
    public function its_supply_category_is_required_when_updating()
    {
        $supplyCategory = SupplyModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'supplyCategory' => Lang::get('validation.required', ['attribute' => 'Category']),
        ]);
    }

    /** @test */
    public function its_supply_category_must_exist_when_creating()
    {
        $response = $this->postJson($this->path, ['supplyCategory' => 9999]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'supplyCategory' => Lang::get('nova::validation.relatable', ['attribute'=>'Category']),
        ]);
    }

    /** @test */
    public function its_supply_category_must_exist_when_updating()
    {
        $supplyCategory = SupplyModel::factory()->create();
        $response       = $this->putJson($this->path . $supplyCategory->getKey(), ['supplyCategory' => 9999]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'supplyCategory' => Lang::get('nova::validation.relatable', ['attribute'=>'Category']),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $response = $this->postJson($this->path, [
            'name'           => 'test value',
            'internal_name'  => 'name',
            'supplyCategory' => SupplyCategory::factory()->create()->getKey(),
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }
}
