<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\CommentVote;
use App\Models\Order;
use App\Models\Scopes\ByUser;
use App\Models\SettingUser;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByUserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_filters_by_user_on_vote_model()
    {
        CommentVote::factory()->count(3)->create();
        CommentVote::factory()->usingUser($this->user)->count(2)->create();

        $this->assertCount(2, CommentVote::scoped(new ByUser($this->user))->get());
    }

    /** @test */
    public function it_filters_by_user_on_setting_user_model()
    {
        SettingUser::factory()->count(3)->create();
        SettingUser::factory()->usingUser($this->user)->count(2)->create();

        $this->assertCount(2, SettingUser::scoped(new ByUser($this->user))->get());
    }

    /** @test */
    public function it_filters_by_user_on_supplier_user_model()
    {
        SupplierUser::factory()->count(3)->createQuietly();
        SupplierUser::factory()->usingUser($this->user)->count(2)->createQuietly();

        $this->assertCount(2, SupplierUser::scoped(new ByUser($this->user))->get());
    }

    /** @test */
    public function it_filters_by_user_on_order_model()
    {
        Order::factory()->count(3)->createQuietly();
        Order::factory()->usingUser($this->user)->count(2)->createQuietly();

        $this->assertCount(2, Order::scoped(new ByUser($this->user))->get());
    }
}
