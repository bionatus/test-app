<?php

namespace Tests\Feature\Nova\Resources\User;

use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lang;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\User\UserSupplier */
class UserSupplierTest extends TestCase
{
    use RefreshDatabase;

    private string $path;
    private string $pathCreateOrUpdate;
    private array  $urlQuery;
    private array  $urlQueryCreateOrUpdate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path               = '/nova-api/' . \App\Nova\Resources\User\UserSupplier::uriKey();
        $this->pathCreateOrUpdate = '/nova-api/' . \App\Nova\Resources\User::uriKey() . '/:userId/:action/user-suppliers';
        $this->urlQuery           = [
            'viaResource'      => \App\Nova\Resources\User::uriKey(),
            'viaRelationship'  => 'suppliers',
            'relationshipType' => 'belongsToMany',
        ];

        $this->urlQueryCreateOrUpdate = [
            'editing'         => true,
            'viaRelationship' => 'suppliers',
            'editMode'        => '',
        ];
    }

    /** @test */
    public function it_displays_a_list_of_suppliers()
    {
        $user = User::factory()->create();
        SupplierUser::factory()->usingUser($user)->count(10)->createQuietly();

        $this->urlQuery['viaResourceId'] = $user->getKey();

        $response = $this->json('get', $this->path, $this->urlQuery);
        $response->assertStatus(Response::HTTP_OK);

        $userSupplier = SupplierUser::all();
        $this->assertCount($response->json('total'), $userSupplier);

        $data = Collection::make($response->json('resources'));

        $firstPageUsers = $userSupplier->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageUsers->pluck('supplier_id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_as_expected_fields()
    {
        $user         = User::factory()->create();
        $supplier     = Supplier::factory()->createQuietly();
        $supplierUser = SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $this->urlQuery['viaResourceId'] = $user->getKey();

        $response = $this->json('get', $this->path, $this->urlQuery);
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $supplier->getKey(),
                'name'      => 'ID',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $supplier->name,
                'name'      => 'Name',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'address',
                'value'     => $supplier->address,
                'name'      => 'Address',
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'visible_by_user',
                'value'     => $supplierUser->visible_by_user,
                'name'      => 'Visible By User',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'orders',
                'value'     => 0,
                'name'      => 'Orders',
            ],
        ];

        $response->assertJson([
            'resources' => [
                [
                    'id'     => [
                        'value' => $supplier->getKey(),
                    ],
                    'fields' => $fields,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_as_expected_fields_when_creating()
    {
        $user = User::factory()->create();

        $path = Lang::get($this->pathCreateOrUpdate, ['userId' => $user->getKey()]);
        $path = Lang::get($path, ['action' => 'creation-pivot-fields']);

        $this->urlQueryCreateOrUpdate['editMode'] = 'attach';

        $response = $this->json('get', $path, $this->urlQueryCreateOrUpdate);
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            'attribute' => 'visible_by_user',
            'value'     => true,
        ];

        $response->assertJson([
            $fields,
        ]);
    }

    /** @test
     * @dataProvider visibleByUserProvider
     */
    public function it_as_expected_fields_when_updating($expected)
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create(['visible_by_user' => $expected]);

        $path = Lang::get($this->pathCreateOrUpdate, ['userId' => $user->getKey()]);
        $path = Lang::get($path, ['action' => 'update-pivot-fields']);
        $path .= '/' . $supplier->getKey();

        $this->urlQueryCreateOrUpdate['editMode'] = 'update-attached';

        $response = $this->json('get', $path, $this->urlQueryCreateOrUpdate);
        $response->assertStatus(Response::HTTP_OK);

        $fields = [
            'attribute' => 'visible_by_user',
            'value'     => $expected,
        ];

        $response->assertJson([
            'fields' => [$fields],
        ]);
    }

    public function visibleByUserProvider(): array
    {
        return [[true], [false]];
    }
}
