<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\User\CompanyUpdated;
use App\Jobs\Hubspot\UpdateCompany;
use App\Listeners\User\UpdateHubspotCompany;
use App\Models\CompanyUser;
use Bus;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class UpdateHubspotCompanyTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateHubspotCompany::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatch_a_job()
    {
        Bus::fake([UpdateCompany::class]);

        $event       = Mockery::mock(CompanyUpdated::class);
        $companyUser = Mockery::mock(CompanyUser::class);

        $event->shouldReceive('companyUser')->withNoArgs()->once()->andReturn($companyUser);

        $listener = App::make(UpdateHubspotCompany::class);
        $listener->handle($event);

        Bus::assertDispatched(UpdateCompany::class, function(UpdateCompany $job) use ($companyUser) {
            $property = new ReflectionProperty($job, 'companyUser');
            $property->setAccessible(true);
            $this->assertSame($companyUser, $property->getValue($job));

            return true;
        });
    }
}
