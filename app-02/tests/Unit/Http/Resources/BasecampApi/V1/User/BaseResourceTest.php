<?php

namespace Tests\Unit\Http\Resources\BasecampApi\V1\User;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Http\Resources\BasecampApi\V1\User\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getFirstMedia')->with('images')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturnNull();
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getPhone')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('totalPointsEarned')->withNoArgs()->once()->andReturn($pointsEarned = 0);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getAttribute')->withArgs(['registration_completed_at'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturnNull();
        $user->shouldReceive('loadCount')->with('orders')->once();
        $user->shouldReceive('loadCount')->with('ordersInProgress')->once();
        $user->shouldReceive('getAttribute')->with('companyUser')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('orders_count')->once()->andReturn($ordersCount = 0);
        $user->shouldReceive('getAttribute')
            ->with('orders_in_progress_count')
            ->once()
            ->andReturn($ordersInProgressCount = 0);

        $resource = new BaseResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'                     => $id,
            'photo'                  => null,
            'avatar'                 => null,
            'first_name'             => $firstName,
            'last_name'              => $lastName,
            'public_name'            => null,
            'disabled'               => false,
            'company'                => null,
            'phone'                  => null,
            'quotes_requested_count' => $ordersCount,
            'orders_count'           => $ordersInProgressCount,
            'earned_points'          => $pointsEarned,
            'accredited'             => false,
            'member_since'           => null,
            'experience'             => null,
            'equipment_type'         => null,
        ];

        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('company name');
        $company->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('company type');
        $company->shouldReceive('getAttribute')->with('country')->once()->andReturn('US');
        $company->shouldReceive('getAttribute')->with('state')->once()->andReturn('US-FL');
        $company->shouldReceive('getAttribute')->with('city')->once()->andReturn('Orlando');
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn('company zip_code');
        $company->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn('address');
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('getRouteKey');

        $companyUser = Mockery::mock(CompanyUser::class);
        $companyUser->shouldReceive('getAttribute')->withArgs(['company'])->once()->andReturn($company);
        $companyUser->shouldReceive('getAttribute')
            ->withArgs(['equipment_type'])
            ->once()
            ->andReturn($equipmentType = 'equipment type');

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('media-uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media-url');
        $media->shouldReceive('hasGeneratedConversion')->with(MediaConversionNames::THUMB)->once()->andReturnFalse();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturn($photo = 'photo.jpg');
        $user->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn($publicName = 'public name');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getPhone')->withNoArgs()->once()->andReturn($fullNumber = '123456789');
        $user->shouldReceive('loadCount')->with('orders')->once();
        $user->shouldReceive('loadCount')->with('ordersInProgress')->once();
        $user->shouldReceive('getAttribute')
            ->with('orders_in_progress_count')
            ->once()
            ->andReturn($orderInProgressCount = 10);
        $user->shouldReceive('getAttribute')->with('orders_count')->once()->andReturn($ordersCount = 20);
        $user->shouldReceive('totalPointsEarned')->withNoArgs()->once()->andReturn($earnedPoints = 30);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getAttribute')
            ->with('registration_completed_at')
            ->once()
            ->andReturn($registrationCompletedAt = 'registration_completed_at');
        $user->shouldReceive('getAttribute')
            ->with('experience_years')
            ->once()
            ->andReturn($experienceYears = 'experience_years');
        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturn($companyUser);

        $response = (new BaseResource($user))->resolve();

        $data = [
            'id'                     => $id,
            'first_name'             => $firstName,
            'last_name'              => $lastName,
            'public_name'            => $publicName,
            'photo'                  => Storage::url($photo),
            'disabled'               => false,
            'avatar'                 => new ImageResource($media),
            'company'                => new CompanyResource($company),
            'phone'                  => $fullNumber,
            'quotes_requested_count' => $ordersCount,
            'orders_count'           => $orderInProgressCount,
            'earned_points'          => $earnedPoints,
            'accredited'             => false,
            'member_since'           => $registrationCompletedAt,
            'experience'             => $experienceYears,
            'equipment_type'         => $equipmentType,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
