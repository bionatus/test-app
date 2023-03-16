<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Profile;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\Account\Profile\BaseResource;
use App\Http\Resources\Api\V3\Account\SupplierCollection;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Media;
use App\Models\Phone;
use App\Models\User;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
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
        $suppliers = new LengthAwarePaginator([], 0, 15, null, [
            'path'     => 'http://localhost',
            'pageName' => 'page',
        ]);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('paginate')->once()->andReturn($suppliers);

        $phoneRelation = Mockery::mock(HasOne::class);
        $phoneRelation->shouldReceive('first')->once()->andReturnNull();

        $suppliersRelation = Mockery::mock(BelongsToMany::class);
        $suppliersRelation->shouldReceive('scoped')->once()->andReturn($builder);

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('getAttribute')->withArgs(['state']);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['bio'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['email'])->once()->andReturn($email = 'email');
        $user->shouldReceive('getAttribute')->withArgs(['experience_years'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->withArgs(['hat_requested'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['registration_completed_at'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['timezone'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturnNull();
        $user->shouldReceive('getFirstMedia')->withArgs([MediaCollectionNames::IMAGES])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('phone')->withAnyArgs()->once()->andReturn($phoneRelation);
        $user->shouldReceive('visibleSuppliers')->withAnyArgs()->once()->andReturn($suppliersRelation);

        $resource = new BaseResource($user);
        $response = $resource->resolve();

        $data = [
            'id'                => $id,
            'photo'             => null,
            'avatar'            => null,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'public_name'       => null,
            'accredited'        => true,
            'address'           => null,
            'address_2'         => null,
            'city'              => null,
            'state'             => null,
            'country'           => null,
            'timezone'          => null,
            'experience'        => null,
            'bio'               => null,
            'member_since'      => null,
            'zip_code'          => null,
            'email'             => $email,
            'hat_requested'     => null,
            'verified'          => true,
            'phone_full_number' => '',
            'job_title'         => null,
            'equipment_type'    => null,
            'company'           => null,
            'suppliers'         => new SupplierCollection($suppliers),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $suppliers = new LengthAwarePaginator([], 0, 15, null, [
            'path'     => 'http://localhost',
            'pageName' => 'page',
        ]);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('paginate')->once()->andReturn($suppliers);

        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn('company city');
        $company->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn('company country');
        $company->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('company state');
        $company->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('company type');
        $company->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn('company state');
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn('company zip_code');
        $company->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn('company address');
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('getRouteKey');

        $companyUser = Mockery::mock(CompanyUser::class);
        $companyUser->shouldReceive('getAttribute')->withArgs(['company'])->once()->andReturn($company);
        $companyUser->shouldReceive('getAttribute')
            ->withArgs(['equipment_type'])
            ->once()
            ->andReturn($equipmentType = 'equipment_type');
        $companyUser->shouldReceive('getAttribute')
            ->withArgs(['job_title'])
            ->once()
            ->andReturn($jobTitle = 'job_title');

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getUrl')->withArgs(['thumb'])->once()->andReturn('media thumb url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnTrue();

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->once()->andReturn($phoneFullNumber = 'fullNumber');

        $phoneRelation = Mockery::mock(HasOne::class);
        $phoneRelation->shouldReceive('first')->once()->andReturn($phone);

        $suppliersRelation = Mockery::mock(BelongsToMany::class);
        $suppliersRelation->shouldReceive('scoped')->once()->andReturn($builder);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address = 'address');
        $user->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2 = 'address_2');
        $user->shouldReceive('getAttribute')->withArgs(['bio'])->once()->andReturn($bio = 'bio');
        $user->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'city');
        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturn($companyUser);
        $user->shouldReceive('getAttribute')
            ->withArgs(['country'])
            ->once()
            ->andReturn($countryCode = CountryDataType::UNITED_STATES);
        $user->shouldReceive('getAttribute')->withArgs(['email'])->once()->andReturn($email = 'email');
        $user->shouldReceive('getAttribute')
            ->withArgs(['experience_years'])
            ->once()
            ->andReturn($experienceYears = 'experience_years');
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->withArgs(['hat_requested'])->once()->andReturntrue();
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->twice()->andReturn($photo = 'photo');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'public_name');
        $user->shouldReceive('getAttribute')
            ->withArgs(['registration_completed_at'])
            ->once()
            ->andReturn($registrationCompletedAt = 'registration_completed_at');
        $user->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->andReturn($stateCode = CountryDataType::UNITED_STATES . '-AR');
        $user->shouldReceive('getAttribute')->withArgs(['timezone'])->once()->andReturn($timezone = 'timezone');
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturn($zip = 'zip');
        $user->shouldReceive('getFirstMedia')->withArgs([MediaCollectionNames::IMAGES])->once()->andReturn($media);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('phone')->withAnyArgs()->once()->andReturn($phoneRelation);
        $user->shouldReceive('visibleSuppliers')->withAnyArgs()->once()->andReturn($suppliersRelation);

        $country = Country::build($countryCode);
        $state   = $country->getStates()->filter(fn(State $state) => $state->isoCode === $stateCode)->first();

        $resource = new BaseResource($user);
        $response = $resource->resolve();

        $data = [
            'id'                => $id,
            'photo'             => Storage::url($photo),
            'avatar'            => new ImageResource($media),
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'public_name'       => $publicName,
            'accredited'        => false,
            'address'           => $address,
            'address_2'         => $address2,
            'city'              => $city,
            'state'             => new StateResource($state),
            'country'           => new CountryResource($country),
            'timezone'          => $timezone,
            'experience'        => $experienceYears,
            'bio'               => $bio,
            'member_since'      => $registrationCompletedAt,
            'zip_code'          => $zip,
            'email'             => $email,
            'hat_requested'     => true,
            'verified'          => false,
            'phone_full_number' => $phoneFullNumber,
            'job_title'         => $jobTitle,
            'equipment_type'    => $equipmentType,
            'company'           => new CompanyResource($company),
            'suppliers'         => new SupplierCollection($suppliers),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
