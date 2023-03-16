<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\PubnubChannel;

use App\Http\Resources\Api\V3\Account\PubnubChannel\BaseResource;
use App\Http\Resources\Models\SupplierResource;
use App\Http\Resources\Models\UserResource;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['latitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['longitude'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('name');
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturnNull();
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('uuid');
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnTrue();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn('first_name');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn('last_name');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'public name');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getAttribute')->withArgs(['supplier'])->once()->andReturn($supplier);
        $pubnubChannel->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);
        $pubnubChannel->shouldReceive('getKey')->withNoArgs()->once()->andReturn($id = 1);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($channel = 'channel');

        $resource = new BaseResource($pubnubChannel);

        $response = $resource->resolve();

        $data = [
            'id'       => $id,
            'channel'  => $channel,
            'supplier' => new SupplierResource($supplier),
            'user'     => new UserResource($user),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
