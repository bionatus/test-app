<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\CompanyUser;
use App\Models\Phone;
use App\Models\Scopes\ByUserId;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByUserIdTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_filters_by_user_id_on_phone_model()
    {
        Phone::factory()->count(2)->create();
        $phone = Phone::factory()->usingUser($this->user)->create();

        $foundPhone = Phone::scoped(new ByUserId($this->user->getKey()))->first();

        $this->assertInstanceOf(Phone::class, $phone);
        $this->assertEquals($phone->getKey(), $foundPhone->getKey());
    }

    /** @test */
    public function it_filters_by_user_id_on_company_user_model()
    {
        CompanyUser::factory()->count(2)->create();
        $phone = CompanyUser::factory()->usingUser($this->user)->create();

        $foundPhone = CompanyUser::scoped(new ByUserId($this->user->getKey()))->first();

        $this->assertInstanceOf(CompanyUser::class, $phone);
        $this->assertEquals($phone->getKey(), $foundPhone->getKey());
    }
}
