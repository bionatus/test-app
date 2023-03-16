<?php

namespace Tests\Unit\Models\Staff;


use App\Models\BrandDetailCounter;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\Order;
use App\Models\OrderStaff;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Staff $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Staff::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }

    /** @test */
    public function it_has_orders()
    {
        OrderStaff::factory()->usingStaff($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }

    /** @test */
    public function it_has_order_staffs()
    {
        OrderStaff::factory()->usingStaff($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->orderStaffs()->get();

        $this->assertCorrectRelation($related, OrderStaff::class);
    }

    /** @test */
    public function it_has_part_detail_counters()
    {
        PartDetailCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->partDetailCounters()->get();

        $this->assertCorrectRelation($related, PartDetailCounter::class);
    }

    /** @test */
    public function it_has_parts()
    {
        PartDetailCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->parts()->get();

        $this->assertCorrectRelation($related, Part::class);
    }

    /** @test */
    public function it_has_oem_detail_counters()
    {
        OemDetailCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemDetailCounters()->get();

        $this->assertCorrectRelation($related, OemDetailCounter::class);
    }

    /** @test */
    public function it_has_oems()
    {
        OemDetailCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oems()->get();

        $this->assertCorrectRelation($related, Oem::class);
    }

    /** @test */
    public function it_has_brand_detail_counters()
    {
        BrandDetailCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brandDetailCounters()->get();

        $this->assertCorrectRelation($related, BrandDetailCounter::class);
    }

    /** @test */
    public function it_has_brands()
    {
        BrandDetailCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brands()->get();

        $this->assertCorrectRelation($related, Brand::class);
    }

    /** @test */
    public function it_has_part_searches()
    {
        PartSearchCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->partSearches()->get();

        $this->assertCorrectRelation($related, PartSearchCounter::class);
    }

    /** @test */
    public function it_has_oem_searches()
    {
        OemSearchCounter::factory()->usingStaff($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemSearches()->get();

        $this->assertCorrectRelation($related, OemSearchCounter::class);
    }

    /** @test */
    public function it_has_setting_staff()
    {
        SettingStaff::factory()->usingStaff($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->settingStaffs()->get();

        $this->assertCorrectRelation($related, SettingStaff::class);
    }
}
