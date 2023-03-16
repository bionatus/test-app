<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Cart;

use App\Http\Resources\Api\V3\Account\Cart\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Cart;
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

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('cartItems')->withNoArgs()->once()->andReturn($hasMany);
        $cart->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($date = Carbon::now());

        $resource = new BaseResource($cart);
        $response = $resource->resolve();
        $data     = [
            'created_at'  => $date,
            'total_items' => $cartItemsCount,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
