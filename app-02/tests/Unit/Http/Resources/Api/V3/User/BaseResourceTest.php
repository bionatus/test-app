<?php

namespace Tests\Unit\Http\Resources\Api\V3\User;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\User\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Media;
use App\Models\User;
use App\Types\CountryDataType;
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
        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('getAttribute')->withArgs(['state']);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('getAttribute')->withArgs(['bio'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['experience_years'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['registration_completed_at'])->once()->andReturnNull();
        $user->shouldReceive('getFirstMedia')->withArgs([MediaCollectionNames::IMAGES])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);

        $response = (new BaseResource($user))->resolve();

        $data = [
            'id'           => $id,
            'photo'        => null,
            'avatar'       => null,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'public_name'  => null,
            'verified'     => true,
            'accredited'   => true,
            'city'         => null,
            'state'        => null,
            'country'      => null,
            'experience'   => null,
            'bio'          => null,
            'company'      => null,
            'job_title'    => null,
            'member_since' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn('company city');
        $company->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('company state');
        $company->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('company type');
        $company->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturnNull();
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn('company zip_code');
        $company->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn('address');
        $company->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('getRouteKey');

        $companyUser = Mockery::mock(CompanyUser::class);
        $companyUser->shouldReceive('getAttribute')->withArgs(['company'])->once()->andReturn($company);
        $companyUser->shouldReceive('getAttribute')
            ->withArgs(['job_title'])
            ->once()
            ->andReturn($jobTitle = 'job_title');

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getUrl')->withArgs(['thumb'])->once()->andReturn('media thumb url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnTrue();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getAttribute')->withArgs(['bio'])->once()->andReturn($bio = 'bio');
        $user->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'city');
        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturn($companyUser);
        $user->shouldReceive('getAttribute')
            ->withArgs(['country'])
            ->once()
            ->andReturn($countryCode = CountryDataType::UNITED_STATES);
        $user->shouldReceive('getAttribute')
            ->withArgs(['experience_years'])
            ->once()
            ->andReturn($experienceYears = 'experience_years');
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'first_name');
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
        $user->shouldReceive('getFirstMedia')->withArgs([MediaCollectionNames::IMAGES])->once()->andReturn($media);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);

        $country = Country::build($countryCode);
        $state   = $country->getStates()->filter(fn(State $state) => $state->isoCode === $stateCode)->first();

        $response = (new BaseResource($user))->resolve();

        $data = [
            'id'           => $id,
            'photo'        => Storage::url($photo),
            'avatar'       => new ImageResource($media),
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'public_name'  => $publicName,
            'verified'     => false,
            'accredited'   => false,
            'city'         => $city,
            'state'        => new StateResource($state),
            'country'      => new CountryResource($country),
            'experience'   => $experienceYears,
            'bio'          => $bio,
            'company'      => new CompanyResource($company),
            'job_title'    => $jobTitle,
            'member_since' => $registrationCompletedAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
