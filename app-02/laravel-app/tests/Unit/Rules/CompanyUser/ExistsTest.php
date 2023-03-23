<?php

namespace Tests\Unit\Rules\CompanyUser;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Rules\CompanyUser\Exists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExistsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->login($this->user);
    }

    /** @test */
    public function it_returns_a_custom_message()
    {
        $rule = new Exists($this->user);

        $this->assertSame('The company must be related to the user.', $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_not_valid_uuid_from_a_company()
    {
        $rule = new Exists($this->user);

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_fails_if_the_company_is_not_related_to_the_authenticated_user()
    {
        $company = Company::factory()->create();
        $rule    = new Exists($this->user);

        $this->assertFalse($rule->passes('attribute', $company->getRouteKey()));
    }

    /** @test */
    public function it_passes()
    {
        $company = Company::factory()->create();
        CompanyUser::factory()->usingUser($this->user)->usingCompany($company)->create();
        $rule = new Exists($this->user);

        $this->assertTrue($rule->passes('attribute', $company->getRouteKey()));
    }
}
