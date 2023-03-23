<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Supplier\User;

use App\Http\Resources\LiveApi\V2\Supplier\User\BaseResource;
use App\Http\Resources\LiveApi\V2\Supplier\User\ChatResource;
use App\Http\Resources\LiveApi\V2\Supplier\User\ImageResource;
use App\Http\Resources\LiveApi\V2\Supplier\User\SupplierUserResource;
use App\Models\Device;
use App\Models\Media;
use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\PushNotificationToken;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $devices = Mockery::mock(Collection::class);
        $devices->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('channel');
        $pubnubChannel->shouldReceive('getAttribute')->with('last_message_at')->once()->andReturn(Carbon::now());

        $pubnubChannels = Mockery::mock(Collection::class);
        $pubnubChannels->shouldReceive('first')->withNoArgs()->once()->andReturn($pubnubChannel);

        $supplierUsers = Mockery::mock(Collection::class);
        $supplierUsers->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $orders = Mockery::mock(Collection::class);
        $orders->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($name = 'supplier name');
        $user->shouldReceive('hasMedia')->withAnyArgs()->once()->andReturnFalse();
        $user->shouldReceive('getAttribute')->with('devices')->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->with('pubnubChannels')->once()->andReturn($pubnubChannels);
        $user->shouldReceive('getAttribute')->with('supplierUsers')->once()->andReturn($supplierUsers);
        $user->shouldReceive('getAttribute')->with('orders')->once()->andReturn($orders);
        $user->shouldReceive('getAttribute')->with('orders_exists')->once()->andReturnFalse();
        $user->shouldReceive('getKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('photoUrl')->withAnyArgs()->once()->andReturnFalse();
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('pending_orders_count')->once()->andReturn($pendingOrdersCount = 1);
        $user->shouldReceive('getAttribute')
            ->with('pending_approval_orders_count')
            ->once()
            ->andReturn($pendingApprovalOrdersCount = 2);

        $resource = new BaseResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'                              => $id,
            'name'                            => $name,
            'company'                         => null,
            'image'                           => null,
            'chat'                            => new ChatResource($pubnubChannel),
            'push_notification_token'         => null,
            'supplier_user'                   => null,
            'oldest_pending_order_created_at' => null,
            'has_working_orders'              => false,
            'pending_orders_count'            => $pendingOrdersCount,
            'pending_approval_orders_count'   => $pendingApprovalOrdersCount,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $pushNotificationToken = Mockery::mock(PushNotificationToken::class);
        $pushNotificationToken->shouldReceive('getAttribute')->with('token')->once()->andReturn($token = 'token');

        $device = Mockery::mock(Device::class);
        $device->shouldReceive('getAttribute')
            ->with('pushNotificationToken')
            ->once()
            ->andReturn($pushNotificationToken);

        $devices = Mockery::mock(\Illuminate\Database\Eloquent\Collection::class);
        $devices->shouldReceive('first')->withNoArgs()->once()->andReturn($device);

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('getUrl')->with('thumb')->twice()->andReturn('media thumb url');
        $media->shouldReceive('getAttribute')->with('uuid')->twice()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturn(true);

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('channel');
        $pubnubChannel->shouldReceive('getAttribute')->with('last_message_at')->once()->andReturn(Carbon::now());

        $pubnubChannels = Mockery::mock(Collection::class);
        $pubnubChannels->shouldReceive('first')->withNoArgs()->once()->andReturn($pubnubChannel);

        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->with('customer_tier')->once()->andReturn('customer tier');
        $supplierUser->shouldReceive('getAttribute')->with('status')->once()->andReturn(SupplierUser::STATUS_CONFIRMED);
        $supplierUser->shouldReceive('getAttribute')->with('cash_buyer')->once()->andReturnFalse();

        $supplierUsers = Mockery::mock(Collection::class);
        $supplierUsers->shouldReceive('first')->withNoArgs()->once()->andReturn($supplierUser);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($orderCreatedAt = Carbon::now());

        $orders = Mockery::mock(Collection::class);
        $orders->shouldReceive('first')->withNoArgs()->once()->andReturn($order);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($name = 'supplier name');
        $user->shouldReceive('hasMedia')->withAnyArgs()->once()->andReturnTrue();
        $user->shouldReceive('getAttribute')->with('devices')->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->with('pubnubChannels')->once()->andReturn($pubnubChannels);
        $user->shouldReceive('getAttribute')->with('supplierUsers')->once()->andReturn($supplierUsers);
        $user->shouldReceive('getAttribute')->with('orders')->once()->andReturn($orders);
        $user->shouldReceive('getAttribute')->with('orders_exists')->once()->andReturnTrue();
        $user->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturn($media);
        $user->shouldReceive('getKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('photoUrl')->withAnyArgs()->once()->andReturnFalse();
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($companyName = 'company name');
        $user->shouldReceive('getAttribute')->with('pending_orders_count')->once()->andReturn($pendingOrdersCount = 1);
        $user->shouldReceive('getAttribute')
            ->with('pending_approval_orders_count')
            ->once()
            ->andReturn($pendingApprovalOrdersCount = 2);

        $resource = new BaseResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'                              => $id,
            'name'                            => $name,
            'company'                         => $companyName,
            'image'                           => new ImageResource($user),
            'chat'                            => new ChatResource($pubnubChannel),
            'push_notification_token'         => $token,
            'supplier_user'                   => new SupplierUserResource($supplierUser),
            'oldest_pending_order_created_at' => $orderCreatedAt,
            'has_working_orders'              => true,
            'pending_orders_count'            => $pendingOrdersCount,
            'pending_approval_orders_count'   => $pendingApprovalOrdersCount,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
