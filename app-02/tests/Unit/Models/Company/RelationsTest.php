<?php

namespace Tests\Unit\Models\Company;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Company $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Company::factory()->create();
    }

    /** @test */
    public function it_has_company_users()
    {
        CompanyUser::factory()->usingCompany($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->companyUsers()->get();

        $this->assertCorrectRelation($related, CompanyUser::class);
    }

    /** @test */
    public function it_has_users()
    {
        CompanyUser::factory()->usingCompany($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_orders()
    {
        Order::factory()->usingCompany($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }
}
