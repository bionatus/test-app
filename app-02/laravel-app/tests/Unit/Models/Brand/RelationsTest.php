<?php

namespace Tests\Unit\Models\Brand;

use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\BrandSupplier;
use App\Models\PartDetailCounter;
use App\Models\Series;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupportCall;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Brand $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Brand::factory()->create();
    }

    /** @test */
    public function it_has_series()
    {
        Series::factory()->usingBrand($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->series()->get();

        $this->assertCorrectRelation($related, Series::class);
    }

    /** @test */
    public function it_has_suppliers()
    {
        BrandSupplier::factory()->usingBrand($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->suppliers()->get();

        $this->assertCorrectRelation($related, Supplier::class);
    }

    /** @test */
    public function it_has_brand_suppliers()
    {
        BrandSupplier::factory()->usingBrand($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->brandSuppliers()->get();

        $this->assertCorrectRelation($related, BrandSupplier::class);
    }

    /** @test */
    public function it_has_brand_detail_counters()
    {
        BrandDetailCounter::factory()->usingBrand($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brandDetailCounters()->get();

        $this->assertCorrectRelation($related, BrandDetailCounter::class);
    }

    /** @test */
    public function it_has_support_calls()
    {
        SupportCall::factory()->missingOemBrand()->usingMissingOemBrand($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supportCalls()->get();

        $this->assertCorrectRelation($related, SupportCall::class);
    }

    /** @test */
    public function it_has_staff()
    {
        BrandDetailCounter::factory()->withStaff()->usingBrand($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->staff()->get();

        $this->assertCorrectRelation($related, Staff::class);
    }

    /** @test */
    public function it_has_users()
    {
        BrandDetailCounter::factory()->withUser()->usingBrand($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

}
