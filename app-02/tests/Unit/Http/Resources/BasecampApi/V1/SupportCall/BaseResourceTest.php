<?php

namespace Tests\Unit\Http\Resources\BasecampApi\V1\SupportCall;

use App\Http\Resources\BasecampApi\V1\SupportCall\BaseResource;
use App\Http\Resources\BasecampApi\V1\SupportCall\OemResource;
use App\Http\Resources\BasecampApi\V1\SupportCall\UserResource;
use App\Http\Resources\Models\BrandResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\User;
use Mockery;
use Tests\TestCase;

/**
 * @property BaseResource $resource
 */
class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_oem_data()
    {
        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('id');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn('a model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturn('a model note');
        $oem->shouldReceive('getAttribute')->with('logo')->once()->andReturn('https://fake-logo');
        $oem->shouldReceive('getAttribute')->with('refrigerant')->once()->andReturn('refrigerant');
        $oem->shouldReceive('getAttribute')->with('unit_image')->once()->andReturn('https://fake-image');
        $oem->shouldReceive('getAttribute')->with('call_group_tags')->once()->andReturn('call group tag fake');
        $oem->shouldReceive('getAttribute')->with('calling_groups')->once()->andReturn('calling groups fake');
        $oem->shouldReceive('functionalPartsCount')->withNoArgs()->once()->andReturn(3);
        $oem->shouldReceive('manualsCount')->withNoArgs()->once()->andReturn(5);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getFirstMedia')->with('images')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturnNull();
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('totalPointsEarned')->withNoArgs()->once()->andReturn(0);
        $user->shouldReceive('loadCount')->with('orders')->once();
        $user->shouldReceive('loadCount')->with('ordersInProgress')->once();
        $user->shouldReceive('getAttribute')->with('companyUser')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('orders_count')->once()->andReturn(0);
        $user->shouldReceive('getAttribute')->with('orders_in_progress_count')->once()->andReturn(0);

        $supportCall = Mockery::mock(SupportCall::class);
        $supportCall->shouldReceive('getAttribute')->withArgs(['category'])->once()->andReturn($category = 'category');
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['created_at'])
            ->once()
            ->andReturn($createdAt = 'created_at');
        $supportCall->shouldReceive('getAttribute')->withArgs(['oem'])->times(3)->andReturn($oem);
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['subcategory'])
            ->once()
            ->andReturn($subcategory = 'subcategory');
        $supportCall->shouldReceive('getAttribute')->withArgs(['user'])->twice()->andReturn($user);
        $supportCall->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1');
        $supportCall->shouldReceive('getAttribute')->withArgs(['missingOemBrand'])->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['missing_oem_model_number'])->once()->andReturnNull();

        $response = (new BaseResource($supportCall))->resolve();

        $data = [
            'id'                       => $id,
            'category'                 => $category,
            'subcategory'              => $subcategory,
            'user'                     => new UserResource($user),
            'oem'                      => new OemResource($oem),
            'missing_oem_brand'        => null,
            'missing_oem_model_number' => null,
            'created_at'               => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_missing_oem_brand_data()
    {
        $id     = '77';
        $name   = 'Brand Name';
        $images = [
            ['id' => '123', 'url' => 'http://image.com'],
        ];

        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->once()->andReturn($images);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getFirstMedia')->with('images')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturnNull();
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('totalPointsEarned')->withNoArgs()->once()->andReturn(0);
        $user->shouldReceive('loadCount')->with('orders')->once();
        $user->shouldReceive('loadCount')->with('ordersInProgress')->once();
        $user->shouldReceive('getAttribute')->with('companyUser')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('orders_count')->once()->andReturn(0);
        $user->shouldReceive('getAttribute')->with('orders_in_progress_count')->once()->andReturn(0);

        $supportCall = Mockery::mock(SupportCall::class);
        $supportCall->shouldReceive('getAttribute')->with('category')->once()->andReturn($category = 'category');
        $supportCall->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = 'created_at');
        $supportCall->shouldReceive('getAttribute')->with('oem')->twice()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['subcategory'])
            ->once()
            ->andReturn($subcategory = 'subcategory');
        $supportCall->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $supportCall->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1');
        $supportCall->shouldReceive('getAttribute')->withArgs(['missingOemBrand'])->once()->andReturn($brand);
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['missing_oem_model_number'])
            ->once()
            ->andReturn($missingOemModelNumber = 'model number fake');

        $response = (new BaseResource($supportCall))->resolve();

        $data = [
            'id'                       => $id,
            'category'                 => $category,
            'subcategory'              => $subcategory,
            'user'                     => new UserResource($user),
            'oem'                      => null,
            'missing_oem_brand'        => new BrandResource($brand),
            'missing_oem_model_number' => $missingOemModelNumber,
            'created_at'               => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getFirstMedia')->with('images')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturnNull();
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('totalPointsEarned')->withNoArgs()->once()->andReturn(0);
        $user->shouldReceive('loadCount')->with('orders')->once();
        $user->shouldReceive('loadCount')->with('ordersInProgress')->once();
        $user->shouldReceive('getAttribute')->with('companyUser')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('orders_count')->once()->andReturn(0);
        $user->shouldReceive('getAttribute')->with('orders_in_progress_count')->once()->andReturn(0);

        $supportCall = Mockery::mock(SupportCall::class);
        $supportCall->shouldReceive('getAttribute')->with('category')->once()->andReturn($category = 'category');
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['created_at'])
            ->once()
            ->andReturn($createdAt = 'created_at');
        $supportCall->shouldReceive('getAttribute')->with('oem')->twice()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['missingOemBrand'])->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['missing_oem_model_number'])->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['user'])->twice()->andReturn($user);
        $supportCall->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1');

        $response = (new BaseResource($supportCall))->resolve();

        $data = [
            'id'                       => $id,
            'category'                 => $category,
            'subcategory'              => null,
            'user'                     => new UserResource($user),
            'oem'                      => null,
            'missing_oem_brand'        => null,
            'missing_oem_model_number' => null,
            'created_at'               => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
