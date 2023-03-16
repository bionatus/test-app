<?php

namespace Tests\Unit\Http\Resources\Api\V4\Account\Company;

use App\Http\Resources\Api\V4\Account\Company\BaseResource;
use App\Http\Resources\Models\CompanyResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user        = User::factory()->create();
        $company     = Company::factory()->create();
        $companyUser = CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();

        $resource = new BaseResource($user);
        $response = $resource->resolve();

        $data = [
            'job_title'      => $companyUser->job_title,
            'equipment_type' => null,
            'company'        => new CompanyResource($company->fresh()),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create([
            'job_title'      => $job_title = 'fake job_title',
            'equipment_type' => $equipment_type = 'equipment_type',
        ]);

        $resource = new BaseResource($user);
        $response = $resource->resolve();

        $data = [
            'job_title'      => $job_title,
            'equipment_type' => $equipment_type,
            'company'        => new CompanyResource($company->fresh()),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
