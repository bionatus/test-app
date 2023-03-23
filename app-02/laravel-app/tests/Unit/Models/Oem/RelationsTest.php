<?php

namespace Tests\Unit\Models\Oem;

use App\Models\ModelType;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemPart;
use App\Models\OemUser;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\Part;
use App\Models\Series;
use App\Models\Staff;
use App\Models\SupportCall;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Oem $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Oem::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_series()
    {
        $related = $this->instance->series()->first();

        $this->assertInstanceOf(Series::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_model_type()
    {
        $related = $this->instance->modelType()->first();

        $this->assertInstanceOf(ModelType::class, $related);
    }

    /** @test */
    public function it_has_parts()
    {
        OemPart::factory()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->parts()->get();

        $this->assertCorrectRelation($related, Part::class);
    }

    /** @test */
    public function it_has_oem_parts()
    {
        OemPart::factory()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemParts()->get();

        $this->assertCorrectRelation($related, OemPart::class);
    }

    /** @test */
    public function it_has_oem_detail_counters()
    {
        OemDetailCounter::factory()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemDetailCounters()->get();

        $this->assertCorrectRelation($related, OemDetailCounter::class);
    }

    /** @test */
    public function it_has_orders()
    {
        Order::factory()->usingOem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }

    /** @test */
    public function it_has_order_snaps()
    {
        OrderSnap::factory()->usingOem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->orderSnaps()->get();

        $this->assertCorrectRelation($related, OrderSnap::class);
    }

    /** @test */
    public function it_has_staff()
    {
        OemDetailCounter::factory()->withStaff()->usingOem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->staff()->get();

        $this->assertCorrectRelation($related, Staff::class);
    }

    /** @test */
    public function it_has_users()
    {
        OemDetailCounter::factory()->withUser()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_oem_users()
    {
        OemUser::factory()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemUsers()->get();

        $this->assertCorrectRelation($related, OemUser::class);
    }

    /** @test */
    public function it_has_users_through_oem_user()
    {
        OemUser::factory()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->pickerUsers()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_support_calls()
    {
        SupportCall::factory()->oem()->usingOem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supportCalls()->get();

        $this->assertCorrectRelation($related, SupportCall::class);
    }
}
