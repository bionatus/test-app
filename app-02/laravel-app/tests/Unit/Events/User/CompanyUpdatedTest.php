<?php

namespace Tests\Unit\Events\User;

use App\Events\User\CompanyUpdated;
use App\Listeners\User\UpdateHubspotCompany;
use App\Models\CompanyUser;
use Tests\TestCase;

class CompanyUpdatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(CompanyUpdated::class, [
            UpdateHubspotCompany::class,
        ]);
    }

    /** @test */
    public function it_returns_its_company_user()
    {
        $companyUser = new CompanyUser();

        $event = new CompanyUpdated($companyUser);

        $this->assertSame($companyUser, $event->companyUser());
    }
}
