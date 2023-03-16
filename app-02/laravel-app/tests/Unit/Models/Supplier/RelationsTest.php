<?php

namespace Tests\Unit\Models\Supplier;

use App\Models\ApiUsage;
use App\Models\BlockedSupplierUser;
use App\Models\Brand;
use App\Models\BrandSupplier;
use App\Models\Cart;
use App\Models\CustomItem;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\PubnubChannel;
use App\Models\RecommendedReplacement;
use App\Models\ServiceLog;
use App\Models\SettingSupplier;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierCompany;
use App\Models\SupplierHour;
use App\Models\SupplierInvitation;
use App\Models\SupplierUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Supplier $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Supplier::factory()->createQuietly();
    }

    /** @test */
    public function it_has_users()
    {
        SupplierUser::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_unconfirmed_users()
    {
        SupplierUser::factory()->usingSupplier($this->instance)->unconfirmed()->count(10)->create();

        $related = $this->instance->unconfirmedUsers()->get();
        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_confirmed_users()
    {
        SupplierUser::factory()->usingSupplier($this->instance)->confirmed()->count(10)->create();

        $related = $this->instance->confirmedUsers()->get();
        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_supplier_users()
    {
        SupplierUser::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->supplierUsers()->get();

        $this->assertCorrectRelation($related, SupplierUser::class);
    }

    /** @test */
    public function it_has_branch_hours()
    {
        SupplierHour::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->supplierHours()->get();

        $this->assertCorrectRelation($related, SupplierHour::class);
    }

    /** @test */
    public function it_belongs_to_a_supplier_company()
    {
        $supplierCompany = SupplierCompany::factory()->create();
        $supplier        = Supplier::factory()->usingSupplierCompany($supplierCompany)->createQuietly();

        $related = $supplier->supplierCompany()->first();

        $this->assertInstanceOf(SupplierCompany::class, $related);
    }

    /** @test */
    public function it_has_brands()
    {
        BrandSupplier::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brands()->get();

        $this->assertCorrectRelation($related, Brand::class);
    }

    /** @test */
    public function it_has_brand_stores()
    {
        BrandSupplier::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brandSuppliers()->get();

        $this->assertCorrectRelation($related, BrandSupplier::class);
    }

    /** @test */
    public function it_has_staff()
    {
        Staff::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->staff()->get();

        $this->assertCorrectRelation($related, Staff::class);
    }

    /** @test */
    public function it_has_a_staff_of_type_contact()
    {
        Staff::factory()->usingSupplier($this->instance)->contact()->create();

        $related = $this->instance->contact()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }

    /** @test */
    public function it_has_a_staff_of_type_accountant()
    {
        Staff::factory()->usingSupplier($this->instance)->accountant()->create();

        $related = $this->instance->accountant()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }

    /** @test */
    public function it_has_a_staff_of_type_manager()
    {
        Staff::factory()->usingSupplier($this->instance)->manager()->create();

        $related = $this->instance->manager()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }

    /** @test */
    public function it_has_staff_of_type_counter()
    {
        Staff::factory()->usingSupplier($this->instance)->counter()->count(2)->create();

        $related = $this->instance->counters()->get();

        $this->assertCorrectRelation($related, Staff::class, null, 2);
    }

    /** @test */
    public function it_has_supplier_invitations()
    {
        SupplierInvitation::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->supplierInvitations()->get();

        $this->assertCorrectRelation($related, SupplierInvitation::class);
    }

    /** @test */
    public function it_has_inviter_users()
    {
        SupplierInvitation::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->inviterUsers()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    public function it_has_orders()
    {
        Order::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }

    /** @test */
    public function it_has_pubnub_channels()
    {
        PubnubChannel::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->pubnubChannels()->get();

        $this->assertCorrectRelation($related, PubnubChannel::class);
    }

    /** @test */
    public function it_has_setting_suppliers()
    {
        SettingSupplier::factory()->usingSupplier($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->settingSuppliers()->get();

        $this->assertCorrectRelation($related, SettingSupplier::class);
    }

    /** @test */
    public function it_has_custom_items()
    {
        CustomItem::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->customItems()->get();

        $this->assertCorrectRelation($related, CustomItem::class);
    }

    /** @test */
    public function it_has_recommended_replacements()
    {
        RecommendedReplacement::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->recommendedReplacements()->get();

        $this->assertCorrectRelation($related, RecommendedReplacement::class);
    }

    /** @test */
    public function it_has_api_usages()
    {
        ApiUsage::factory()->usingSupplier($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->apiUsages()->get();

        $this->assertCorrectRelation($related, ApiUsage::class);
    }

    /** @test */
    public function it_has_service_logs()
    {
        ServiceLog::factory()->usingSupplier($this->instance)->count(self::COUNT)->createQuietly();
        $related = $this->instance->serviceLogs()->get();

        $this->assertCorrectRelation($related, ServiceLog::class);
    }

    /** @test */
    public function it_has_carts()
    {
        Cart::factory()->usingSupplier($this->instance)->count(10)->create();

        $related = $this->instance->carts()->get();

        $this->assertCorrectRelation($related, Cart::class);
    }

    public function it_has_order_snaps()
    {
        OrderSnap::factory()->usingSupplier($this->instance)->count(10)->createQuietly();

        $related = $this->instance->orderSnaps()->get();

        $this->assertCorrectRelation($related, OrderSnap::class);
    }
}
