<?php

namespace Tests\Unit\Http\Resources\Api\V4\Account\Cart;

use App\Http\Resources\Api\V4\Account\Cart\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Api\V4\Account\Cart\SupplierResource;
use App\Models\Cart;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($cartItemsCount = 0);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('uuid');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnFalse();
        $supplier->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('published_at')->once()->andReturnNull();

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('cartItems')->withNoArgs()->once()->andReturn($hasMany);
        $cart->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date = Carbon::now());
        $cart->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);

        $resource = new BaseResource($cart);
        $response = $resource->resolve();
        $data     = [
            'created_at'  => $date,
            'total_items' => $cartItemsCount,
            'supplier'    => new SupplierResource($supplier),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_without_supplier()
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($cartItemsCount = 0);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('cartItems')->withNoArgs()->once()->andReturn($hasMany);
        $cart->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date = Carbon::now());
        $cart->shouldReceive('getAttribute')->with('supplier')->andReturnNull();

        $resource = new BaseResource($cart);
        $response = $resource->resolve();
        $data     = [
            'created_at'  => $date,
            'total_items' => $cartItemsCount,
            'supplier'    => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(true), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
