<?php

namespace Tests\Unit\Models\User;

use App;
use App\AppNotification;
use App\Models\Agent;
use App\Models\ApiUsage;
use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\Cart;
use App\Models\CartSupplyCounter;
use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\CommentVote;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\CustomItem;
use App\Models\Device;
use App\Models\InternalNotification;
use App\Models\Level;
use App\Models\LevelUser;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\OemSearchCounter;
use App\Models\OemUser;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartSearchCounter;
use App\Models\PendingApprovalView;
use App\Models\Phone;
use App\Models\Point;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\PubnubChannel;
use App\Models\PushNotificationToken;
use App\Models\RelatedActivity;
use App\Models\Series;
use App\Models\SeriesUser;
use App\Models\ServiceLog;
use App\Models\Session;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\SharedOrder;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\SupplierListView;
use App\Models\SupplierUser;
use App\Models\SupplySearchCounter;
use App\Models\SupplyCategoryView;
use App\Models\SupportCall;
use App\Models\Term;
use App\Models\TermUser;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserTaggable;
use App\Models\VideoElapsedTime;
use App\Models\Wishlist;
use App\Services\Hubspot\Hubspot;
use Database\Factories\AppNotificationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property User $instance
 */
class RelationsTest extends RelationsTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = User::factory()->create();
    }

    /** @test */
    public function it_has_posts()
    {
        Post::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->posts()->get();

        $this->assertCorrectRelation($related, Post::class);
    }

    /** @test */
    public function it_has_comments()
    {
        Comment::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->comments()->get();

        $this->assertCorrectRelation($related, Comment::class);
    }

    /** @test */
    public function it_is_an_agent()
    {
        Agent::factory()->usingUser($this->instance)->create();

        $related = $this->instance->agent()->first();

        $this->assertInstanceOf(Agent::class, $related);
    }

    /** @test */
    public function it_has_push_notification_tokens()
    {
        $devices = Device::factory()->usingUser($this->instance)->count(self::COUNT)->create();
        $devices->each(function($device) {
            PushNotificationToken::factory()->usingDevice($device)->create();
        });

        $related = $this->instance->pushNotificationTokens()->get();

        $this->assertCorrectRelation($related, PushNotificationToken::class);
    }

    /** @test */
    public function it_has_followed_tags()
    {
        UserTaggable::factory()->usingUser($this->instance)->count(10)->create();

        $related = $this->instance->followedTags()->get();

        $this->assertCorrectRelation($related, UserTaggable::class);
    }

    /** @test */
    public function it_has_points()
    {
        Point::factory()->usingUser($this->instance)->count(self::COUNT)->createQuietly();
        $related = $this->instance->points()->get();

        $this->assertCorrectRelation($related, Point::class);
    }

    /** @test */
    public function it_has_tickets()
    {
        Ticket::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->tickets()->get();

        $this->assertCorrectRelation($related, Ticket::class);
    }

    /** @test */
    public function it_has_sessions()
    {
        Session::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->sessions()->get();

        $this->assertCorrectRelation($related, Session::class);
    }

    /** @test */
    public function it_has_comment_votes()
    {
        CommentVote::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->commentVotes()->get();

        $this->assertCorrectRelation($related, CommentVote::class);
    }

    /** @test */
    public function it_has_related_activity()
    {
        RelatedActivity::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->relatedActivity()->get();

        $this->assertCorrectRelation($related, RelatedActivity::class);
    }

    /** @test */
    public function it_has_settings()
    {
        SettingUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->settings()->get();

        $this->assertCorrectRelation($related, Setting::class);
    }

    /** @test */
    public function it_has_setting_users()
    {
        SettingUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->settingUsers()->get();

        $this->assertCorrectRelation($related, SettingUser::class);
    }

    /** @test */
    public function it_has_internal_notifications()
    {
        InternalNotification::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->internalNotifications()->get();

        $this->assertCorrectRelation($related, InternalNotification::class);
    }

    /** @test */
    public function it_has_app_notifications()
    {
        (new AppNotificationFactory)->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->appNotifications()->get();

        $this->assertCorrectRelation($related, AppNotification::class);
    }

    /** @test */
    public function it_has_devices()
    {
        Device::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->devices()->get();

        $this->assertCorrectRelation($related, Device::class);
    }

    /** @test */
    public function it_has_a_phone()
    {
        Phone::factory()->usingUser($this->instance)->create();

        $related = $this->instance->phone()->first();

        $this->assertInstanceOf(Phone::class, $related);
    }

    /** @test */
    public function it_has_suppliers()
    {
        SupplierUser::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->suppliers()->get();

        $this->assertCorrectRelation($related, Supplier::class);
    }

    /** @test */
    public function it_has_suppliers_with_pivot_data()
    {
        SupplierUser::factory()->usingUser($this->instance)->createQuietly(['visible_by_user' => false]);

        $related = $this->instance->suppliers()->first();

        $this->assertEquals($this->instance->getKey(), $related->pivot->user_id);
        $this->assertEquals(false, $related->pivot->visible_by_user);
    }

    /** @test */
    public function it_has_visible_suppliers()
    {
        SupplierUser::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->visibleSuppliers()->get();

        $this->assertCorrectRelation($related, Supplier::class);
    }

    /** @test */
    public function it_has_supplier_users()
    {
        SupplierUser::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->supplierUsers()->get();

        $this->assertCorrectRelation($related, SupplierUser::class);
    }

    /** @test */
    public function it_has_visible_supplier_users()
    {
        SupplierUser::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->visibleSupplierUsers()->get();

        $this->assertCorrectRelation($related, SupplierUser::class);
    }

    /** @test */
    public function it_has_series_users()
    {
        SeriesUser::factory()->usingUser($this->instance)->count(10)->create();

        $related = $this->instance->seriesUsers()->get();

        $this->assertCorrectRelation($related, SeriesUser::class);
    }

    /** @test */
    public function it_has_favorite_series()
    {
        SeriesUser::factory()->usingUser($this->instance)->count(10)->create();

        $related = $this->instance->favoriteSeries()->get();

        $this->assertCorrectRelation($related, Series::class);
    }

    /** @test */
    public function it_has_a_company_user()
    {
        CompanyUser::factory()->usingUser($this->instance)->create();

        $related = $this->instance->companyUser()->first();

        $this->assertInstanceOf(CompanyUser::class, $related);
    }

    /** @test */
    public function it_has_a_company()
    {
        CompanyUser::factory()->usingUser($this->instance)->create();

        $related = $this->instance->company()->first();

        $this->assertInstanceOf(Company::class, $related);
    }

    /** @test */
    public function it_has_supplier_invitations()
    {
        SupplierInvitation::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->supplierInvitations()->get();

        $this->assertCorrectRelation($related, SupplierInvitation::class);
    }

    /** @test */
    public function it_has_invited_suppliers()
    {
        SupplierInvitation::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->invitedSuppliers()->get();

        $this->assertCorrectRelation($related, Supplier::class);
    }

    /** @test */
    public function it_has_orders()
    {
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }

    /** @test */
    public function it_has_pubnub_channels()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->times(self::COUNT)->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        PubnubChannel::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->pubnubChannels()->get();

        $this->assertCorrectRelation($related, PubnubChannel::class);
    }

    /** @test */
    public function it_has_part_detail_counters()
    {
        PartDetailCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->partDetailCounters()->get();

        $this->assertCorrectRelation($related, PartDetailCounter::class);
    }

    /** @test */
    public function it_has_parts()
    {
        PartDetailCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->parts()->get();

        $this->assertCorrectRelation($related, Part::class);
    }

    /** @test */
    public function it_has_oem_detail_counters()
    {
        OemDetailCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemDetailCounters()->get();

        $this->assertCorrectRelation($related, OemDetailCounter::class);
    }

    /** @test */
    public function it_has_oems()
    {
        OemDetailCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oems()->get();

        $this->assertCorrectRelation($related, Oem::class);
    }

    /** @test */
    public function it_has_level_users()
    {
        LevelUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->levelUsers()->get();

        $this->assertCorrectRelation($related, LevelUser::class);
    }

    /** @test */
    public function it_has_levels()
    {
        LevelUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->levels()->get();

        $this->assertCorrectRelation($related, Level::class);
    }

    /** @test */
    public function it_has_oem_users()
    {
        OemUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemUsers()->get();

        $this->assertCorrectRelation($related, OemUser::class);
    }

    /** @test */
    public function it_has_terms()
    {
        TermUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->terms()->get();

        $this->assertCorrectRelation($related, Term::class);
    }

    /** @test */
    public function it_has_term_users()
    {
        TermUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->termUsers()->get();

        $this->assertCorrectRelation($related, TermUser::class);
    }

    /** @test */
    public function it_has_oems_through_oem_user()
    {
        OemUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->favoriteOems()->get();

        $this->assertCorrectRelation($related, Oem::class);
    }

    /** @test */
    public function it_has_post_votes()
    {
        PostVote::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->postVotes()->get();

        $this->assertCorrectRelation($related, PostVote::class);
    }

    /** @test */
    public function it_has_shared_orders()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($this->instance)->create();
        SharedOrder::factory()->usingUser($this->instance)->usingOrder($order)->count(self::COUNT)->create();

        $related = $this->instance->sharedOrders()->get();

        $this->assertCorrectRelation($related, SharedOrder::class);
    }

    /** @test */
    public function it_has_custom_items()
    {
        CustomItem::factory()->usingUser($this->instance)->count(self::COUNT)->create();
        $related = $this->instance->customItems()->get();

        $this->assertCorrectRelation($related, CustomItem::class);
    }

    /** @test */
    public function it_has_pending_approval_views()
    {
        $supplier = Supplier::factory()->createQuietly();
        $orders   = Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($this->instance)
            ->count(self::COUNT)
            ->create();

        $orders->each(fn(Order $order) => PendingApprovalView::factory()
            ->usingUser($this->instance)
            ->usingOrder($order)
            ->create());

        $related = $this->instance->pendingApprovalViews()->get();

        $this->assertCorrectRelation($related, PendingApprovalView::class);
    }

    /** @test */
    public function it_has_support_calls()
    {
        SupportCall::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supportCalls()->get();

        $this->assertCorrectRelation($related, SupportCall::class);
    }

    /** @test */
    public function it_has_oem_searches()
    {
        OemSearchCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemSearches()->get();

        $this->assertCorrectRelation($related, OemSearchCounter::class);
    }

    /** @test */
    public function it_has_part_searches()
    {
        PartSearchCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->partSearches()->get();

        $this->assertCorrectRelation($related, PartSearchCounter::class);
    }

    /** @test */
    public function it_tagged_in_comments()
    {
        CommentUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->taggedInComments()->get();

        $this->assertCorrectRelation($related, Comment::class);
    }

    /** @test */
    public function it_has_comment_users()
    {
        CommentUser::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->commentUsers()->get();

        $this->assertCorrectRelation($related, CommentUser::class);
    }

    /** @test */
    public function it_has_supplier_list_views()
    {
        SupplierListView::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supplierListViews()->get();

        $this->assertCorrectRelation($related, SupplierListView::class);
    }

    /** @test */
    public function it_has_api_usages()
    {
        ApiUsage::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->apiUsages()->get();

        $this->assertCorrectRelation($related, ApiUsage::class);
    }

    /** @test */
    public function it_has_a_cart()
    {
        Cart::factory()->usingUser($this->instance)->create();

        $related = $this->instance->cart()->first();

        $this->assertInstanceOf(Cart::class, $related);
    }

    /** @test */
    public function it_has_video_elapsed_times()
    {
        VideoElapsedTime::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->videoElapsedTimes()->get();

        $this->assertCorrectRelation($related, VideoElapsedTime::class);
    }

    /** @test */
    public function it_has_service_logs()
    {
        ServiceLog::factory()->usingUser($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->serviceLogs()->get();

        $this->assertCorrectRelation($related, ServiceLog::class);
    }

    public function it_has_supply_category_views()
    {
        SupplyCategoryView::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supplyCategoryViews()->get();

        $this->assertCorrectRelation($related, SupplyCategoryView::class);
    }

    /** @test */
    public function it_has_cart_supply_counters()
    {
        CartSupplyCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->cartSupplyCounters()->get();

        $this->assertCorrectRelation($related, CartSupplyCounter::class);
    }

    /** @test */
    public function it_has_wishlists()
    {
        Wishlist::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->wishlists()->get();

        $this->assertCorrectRelation($related, Wishlist::class);
    }

    /** @test */
    public function it_has_brand_detail_counters()
    {
        BrandDetailCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brandDetailCounters()->get();

        $this->assertCorrectRelation($related, BrandDetailCounter::class);
    }

    /** @test */
    public function it_has_brands()
    {
        BrandDetailCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->brands()->get();

        $this->assertCorrectRelation($related, Brand::class);
    }

    public function it_has_order_snaps()
    {
        OrderSnap::factory()->usingUser($this->instance)->count(10)->createQuietly();

        $related = $this->instance->orderSnaps()->get();

        $this->assertCorrectRelation($related, OrderSnap::class);
    }

    /** @test */
    public function it_has_supply_searches()
    {
        SupplySearchCounter::factory()->usingUser($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->supplySearches()->get();

        $this->assertCorrectRelation($related, SupplySearchCounter::class);
    }
}
