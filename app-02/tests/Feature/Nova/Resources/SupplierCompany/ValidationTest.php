<?php

namespace Tests\Feature\Nova\Resources\SupplierCompany;

use App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . App\Nova\Resources\SupplierCompany::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $response = $this->postJson($this->path, [
            'name'  => 'A name',
            'email' => 'store@email.com',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }
}
