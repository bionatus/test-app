<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Supplier\Channel;

use App\Http\Resources\Api\V3\Account\Supplier\Channel\BaseResource;
use App\Http\Resources\Api\V3\Account\Supplier\Channel\ImageResource;
use App\Http\Resources\Api\V3\Account\Supplier\Channel\OrderResource;
use App\Models\Media;
use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $orders = Mockery::mock(Collection::class);
        $orders->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $pubnubChannels = Mockery::mock(Collection::class);
        $pubnubChannels->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $supplier->shouldReceive('getAttribute')->withArgs(['orders'])->once()->andReturn($orders);
        $supplier->shouldReceive('getAttribute')->withArgs(['contact_phone'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['pubnubChannels'])->once()->andReturn($pubnubChannels);
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->once()->andReturnNull();

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'         => $uuid,
            'name'       => $name,
            'logo'       => null,
            'address'    => null,
            'address_2'  => null,
            'city'       => null,
            'phone'      => null,
            'channel'    => null,
            'last_order' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('order uuid');
        $order->shouldReceive('getAttribute')->withArgs(['updated_at'])->once()->andReturn('date');

        $orders = Mockery::mock(Collection::class);
        $orders->shouldReceive('first')->withNoArgs()->once()->andReturn($order);

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($pubnubChannelRouteKey = 'channel routeKey');

        $pubnubChannels = Mockery::mock(Collection::class);
        $pubnubChannels->shouldReceive('first')->withNoArgs()->once()->andReturn($pubnubChannel);

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturn(false);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address = 'address');
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2 = 'address_2');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'city');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $supplier->shouldReceive('getAttribute')->withArgs(['orders'])->once()->andReturn($orders);
        $supplier->shouldReceive('getAttribute')->withArgs(['contact_phone'])->once()->andReturn($phone = 'phone');
        $supplier->shouldReceive('getAttribute')->withArgs(['pubnubChannels'])->once()->andReturn($pubnubChannels);
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->once()->andReturn($media);

        $resource = new BaseResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'         => $uuid,
            'name'       => $name,
            'logo'       => new ImageResource($media),
            'address'    => $address,
            'address_2'  => $address2,
            'city'       => $city,
            'phone'      => $phone,
            'channel'    => $pubnubChannelRouteKey,
            'last_order' => new OrderResource($order),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
