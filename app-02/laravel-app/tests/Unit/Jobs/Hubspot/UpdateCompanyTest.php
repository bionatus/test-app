<?php

namespace Tests\Unit\Jobs\Hubspot;

use App\Jobs\Hubspot\UpdateCompany;
use App\Models\CompanyUser;
use App\Services\Hubspot\Hubspot;
use Exception;
use HubSpot\Client\Crm\Contacts\ApiException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class UpdateCompanyTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateCompany::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateCompany(new CompanyUser());

        $this->assertEquals('database', $job->connection);
    }

    /** @test
     * @throws ApiException
     */
    public function it_updates_a_contact_in_hubspot()
    {
        $companyUser = Mockery::mock(CompanyUser::class);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('updateUserCompany')->withArgs([$companyUser])->once();

        $job = new UpdateCompany($companyUser);
        $job->handle($hubspot);
    }

    /** @test
     * @throws ApiException
     */
    public function it_throws_an_exception_when_hubspot_failed()
    {
        $companyUser = Mockery::mock(CompanyUser::class);

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('updateUserCompany')->withArgs([$companyUser])->once()->andThrow(Exception::class);

        $job = new UpdateCompany($companyUser);

        $this->expectException(Exception::class);
        $job->handle($hubspot);
    }
}
