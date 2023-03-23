<?php

namespace Tests\Unit\Models\CompanyUser;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CompanyUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CompanyUser::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_company()
    {
        $related = $this->instance->company()->first();

        $this->assertInstanceOf(Company::class, $related);
    }
}
