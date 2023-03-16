<?php

namespace Tests\Feature\Nova\Resources;

use App;
use App\Models\SupplierCompany;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\SupplierCompany */
class SupplierCompanyTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/nova-api/' . App\Nova\Resources\SupplierCompany::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_supplier_companies()
    {
        $supplierCompanies = SupplierCompany::factory()->withEmail()->count(40)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->create()->sortBy(function(SupplierCompany $supplierCompany) {
            return Str::lower($supplierCompany->name);
        });

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $supplierCompanies);

        $data = Collection::make($response->json('resources'));

        $firstPageSupplierCompanies = $supplierCompanies->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageSupplierCompanies->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_creates_a_supplier_company()
    {
        $response = $this->postJson($this->path, [
            'name'  => $name = 'A new company',
            'email' => $email = 'company@email.com',
        ]);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierCompany::tableName(), ['name' => $name, 'email' => $email]);
    }

    /** @test * */
    public function a_supplier_company_can_be_retrieved_with_correct_resource_elements()
    {
        $company = SupplierCompany::factory()->withEmail()->create();

        $response = $this->getJson($this->path . $company->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $company->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $company->name,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'email',
                'value'     => $company->email,
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $company->name,
            'resource' => [
                'id'     => [
                    'value' => $company->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_a_supplier_company()
    {
        $company = SupplierCompany::factory()->withEmail()->create();

        $fieldsToUpdate = Collection::make([
            'name'  => 'A name',
            'email' => 'email@store.com',
        ]);

        $response = $this->putJson($this->path . $company->getKey(), $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(SupplierCompany::tableName(),
            $fieldsToUpdate->put('id', $company->getKey())->toArray());
    }

    /** @test */
    public function it_destroy_a_supplier_company()
    {
        $company = SupplierCompany::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $company->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($company);
    }
}
