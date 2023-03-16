<?php

namespace Tests\Feature\Api\V3\Account\BriefSupplier;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V3\Account\Supplier\BriefResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see BriefSupplierController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_BRIEF_SUPPLIER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_suppliers_ordered_by_preferred_and_distance()
    {
        $company     = Company::factory()->createQuietly([
            'country'   => CountryDataType::UNITED_STATES,
            'zip_code'  => '90210',
            'latitude'  => 0,
            'longitude' => 0,
        ]);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();
        $user        = $companyUser->user;

        $route = URL::route($this->routeName);

        $suppliersFirst = Supplier::factory()->has(SupplierUser::factory()->usingUser($user))->createManyQuietly([
            [
                'name'         => 'E',
                'latitude'     => '1.3',
                'longitude'    => '1.3',
                'published_at' => '2022-06-18 14:20:00',
            ],
            [
                'name'         => 'F',
                'latitude'     => '1.4',
                'longitude'    => '1.4',
                'published_at' => '2022-06-16 14:20:00',
            ],
            [
                'name'      => 'C',
                'latitude'  => '1.4',
                'longitude' => '1.4',
            ],
            [
                'name'      => 'D',
                'latitude'  => '1.4',
                'longitude' => '1.4',
            ],
        ]);

        $suppliersSecond = Supplier::factory()->has(SupplierUser::factory()->usingUser($user))->createManyQuietly([
            [
                'latitude'  => '0',
                'longitude' => '0',
            ],
            [
                'name'      => 'B',
                'latitude'  => '1.2',
                'longitude' => '1.2',
            ],
        ]);

        $supplierThird = Supplier::factory()->has(SupplierUser::factory()->usingUser($user))->createQuietly([
            'latitude'  => null,
            'longitude' => null,
        ]);

        $supplierPreferred = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplierPreferred)->create([
            'preferred' => true,
        ]);

        $allSuppliers = $suppliersSecond->merge($suppliersFirst)->push($supplierThird)->prepend($supplierPreferred);

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BriefResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $allSuppliers);

        $data = Collection::make($response->json('data'));

        Collection::make([
            Collection::make([$supplierPreferred]),
            $suppliersSecond,
            $suppliersFirst,
            Collection::make([$supplierThird]),
        ])->each(function(Collection $collection) use ($data) {
            $length = $collection->count();
            $chunk  = $data->splice(0, $length)->pluck(Supplier::keyName());
            $this->assertEqualsCanonicalizing($collection->pluck('uuid')->toArray(), $chunk->toArray());
        });
    }

    /** @test */
    public function it_displays_a_list_of_visible_suppliers()
    {
        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        SupplierUser::factory()->usingUser($user)->count(4)->notVisible()->createQuietly();
        $visibleSuppliers = SupplierUser::factory()->count(2)->usingUser($user)->createQuietly()->pluck('supplier');

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BriefResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $visibleSuppliers);

        $data = Collection::make($response->json('data'));

        $this->assertEqualsCanonicalizing($data->pluck('id'), $visibleSuppliers->pluck('uuid'));
    }
}
