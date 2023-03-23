<?php

namespace Tests\Unit\Http\Resources\Api\V3\SupportCall;

use App\Http\Resources\Api\V3\SupportCall\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\BrandResource;
use App\Http\Resources\Models\OemResource;
use App\Http\Resources\Models\SupportCallResource;
use App\Http\Resources\Models\UserResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\User;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(SupportCallResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $supportCall = Mockery::mock(SupportCall::class);
        $supportCall->shouldReceive('getAttribute')->with('category')->once()->andReturn($category = 'category');
        $supportCall->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = 'created_at');
        $supportCall->shouldReceive('getAttribute')->with('oem')->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['missingOemBrand'])->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->withArgs(['missing_oem_model_number'])->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->with('subcategory')->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();
        $supportCall->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1');

        $response = (new BaseResource($supportCall))->resolve();

        $data = [
            'id'                       => $id,
            'category'                 => $category,
            'subcategory'              => null,
            'user'                     => null,
            'oem'                      => null,
            'missing_oem_brand'        => null,
            'missing_oem_model_number' => null,
            'created_at'               => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_oem_data()
    {
        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('functionalPartsCount')->withNoArgs()->once()->andReturn(1);
        $oem->shouldReceive('getAttribute')->with('logo')->once()->andReturn('logo');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn('model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturnNull();
        $oem->shouldReceive('getAttribute')->with('unit_image')->once()->andReturnNull();
        $oem->shouldReceive('getAttribute')->with('call_group_tags')->once()->andReturn('call group tag fake');
        $oem->shouldReceive('getAttribute')->with('calling_groups')->once()->andReturn('calling groups fake');
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('route key');
        $oem->shouldReceive('manualsCount')->withNoArgs()->once()->andReturn(1);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn('public_name');
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $supportCall = Mockery::mock(SupportCall::class);
        $supportCall->shouldReceive('getAttribute')->with('category')->once()->andReturn($category = 'category');
        $supportCall->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = 'created_at');
        $supportCall->shouldReceive('getAttribute')->with('oem')->once()->andReturn($oem);
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['subcategory'])
            ->once()
            ->andReturn($subcategory = 'subcategory');
        $supportCall->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
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
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn('public_name');
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $supportCall = Mockery::mock(SupportCall::class);
        $supportCall->shouldReceive('getAttribute')->with('category')->once()->andReturn($category = 'category');
        $supportCall->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = 'created_at');
        $supportCall->shouldReceive('getAttribute')->with('oem')->once()->andReturnNull();
        $supportCall->shouldReceive('getAttribute')
            ->withArgs(['subcategory'])
            ->once()
            ->andReturn($subcategory = 'subcategory');
        $supportCall->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
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
}
