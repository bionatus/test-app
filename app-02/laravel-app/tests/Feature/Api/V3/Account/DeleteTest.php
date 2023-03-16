<?php

namespace Tests\Feature\Api\V3\Account;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\AccountController;
use App\Jobs\Supplier\UpdateCustomersCounter;
use App\Jobs\Supplier\UpdateTotalCustomers;
use App\Jobs\User\DeleteFirebaseNode;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\CompanyUser;
use App\Models\Device;
use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Phone;
use App\Models\Post;
use App\Models\PubnubChannel;
use App\Models\SeriesUser;
use App\Models\Session;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\SupplierUser;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserTaggable;
use Bus;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see AccountController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName));
    }

    /** @test */
    public function it_deletes_the_user_entry()
    {
        $user = User::factory()->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(User::tableName(), ['id' => $user->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_all_the_comments_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Comment::factory()->usingUser($user)->create();
        Comment::factory()->usingUser($anotherUser)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Comment::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Comment::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_all_the_comment_votes_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        CommentVote::factory()->usingUser($user)->count(2)->create();
        CommentVote::factory()->usingUser($anotherUser)->count(2)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(CommentVote::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(CommentVote::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_company_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        CompanyUser::factory()->usingUser($user)->create();
        CompanyUser::factory()->usingUser($anotherUser)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(CompanyUser::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(CompanyUser::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_device_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Device::factory()->usingUser($user)->count(3)->create();
        Device::factory()->usingUser($anotherUser)->count(2)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Device::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Device::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_internal_notifications_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        InternalNotification::factory()->usingUser($user)->count(2)->create();
        InternalNotification::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(InternalNotification::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(InternalNotification::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_phone_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Phone::factory()->usingUser($user)->create();
        Phone::factory()->usingUser($anotherUser)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Phone::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Phone::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_posts_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Post::factory()->usingUser($user)->count(4)->create();
        Post::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Post::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Post::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_series_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SeriesUser::factory()->usingUser($user)->count(2)->create();
        SeriesUser::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(SeriesUser::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(SeriesUser::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_sessions_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Session::factory()->usingUser($user)->count(2)->create();
        Session::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Session::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Session::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_settings_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SettingUser::factory()->usingUser($user)->count(2)->create();
        SettingUser::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(SettingUser::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(SettingUser::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_supplier_invitations_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SupplierInvitation::factory()->usingUser($user)->count(2)->createQuietly();
        SupplierInvitation::factory()->usingUser($anotherUser)->count(3)->createQuietly();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(SupplierInvitation::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(SupplierInvitation::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_suppliers_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SupplierUser::factory()->usingUser($user)->count(2)->createQuietly();
        SupplierUser::factory()->usingUser($anotherUser)->count(3)->createQuietly();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(SupplierUser::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(SupplierUser::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_orders_related()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->markTestSkipped();
        }

        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Order::factory()->usingUser($user)->pending()->count(2)->createQuietly();
        Order::factory()->usingUser($anotherUser)->pending()->count(3)->createQuietly();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Order::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Order::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_pubnub_channels_related()
    {
        $this->markTestSkipped();

        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        PubnubChannel::factory()->usingUser($user)->createQuietly(['channel' => 'user-fake-channel']);
        PubnubChannel::factory()->usingUser($anotherUser)->createQuietly(['channel' => 'another-user-fake-channel']);

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(PubnubChannel::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(PubnubChannel::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_tickets_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        Ticket::factory()->usingUser($user)->count(2)->create();
        Ticket::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Ticket::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(Ticket::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_taggable_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        UserTaggable::factory()->usingUser($user)->create();
        UserTaggable::factory()->usingUser($anotherUser)->count(3)->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(UserTaggable::tableName(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas(UserTaggable::tableName(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_node_in_firebase_database()
    {
        Bus::fake([DeleteFirebaseNode::class, UpdateCustomersCounter::class, UpdateTotalCustomers::class]);

        $user = User::factory()->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        Bus::assertDispatched(function(DeleteFirebaseNode $job) use ($user) {
            $reflectionProperty = new ReflectionProperty(DeleteFirebaseNode::class, 'userId');
            $reflectionProperty->setAccessible(true);

            $this->assertSame($user->getKey(), $reflectionProperty->getValue($job));

            return true;
        });
    }

    /** @test */
    public function it_updates_the_customers_counters_for_each_related_supplier()
    {
        Bus::fake([DeleteFirebaseNode::class, UpdateCustomersCounter::class, UpdateTotalCustomers::class]);

        $user = User::factory()->create();

        $suppliers = Supplier::factory()->count($times = 5)->createQuietly();
        $suppliers->each(fn(Supplier $supplier) => SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->create());

        Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        Bus::assertDispatched(function(UpdateCustomersCounter $job) use ($suppliers) {
            $reflectionProperty = new ReflectionProperty(UpdateCustomersCounter::class, 'supplier');
            $reflectionProperty->setAccessible(true);

            /** @var Supplier $supplierInJob */
            $supplierInJob = $reflectionProperty->getValue($job);

            return $suppliers->pluck(Supplier::keyName())->contains($supplierInJob->getKey());
        });
        Bus::assertDispatchedTimes(UpdateCustomersCounter::class, $times);
    }

    /** @test */
    public function it_updates_the_total_customers_counters_for_each_related_supplier()
    {
        Bus::fake([DeleteFirebaseNode::class, UpdateCustomersCounter::class, UpdateTotalCustomers::class]);

        $user = User::factory()->create();

        $suppliers = Supplier::factory()->count($times = 5)->createQuietly();
        $suppliers->each(fn(Supplier $supplier) => SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->create());

        Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();

        $route = Url::route($this->routeName);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        Bus::assertDispatched(function(UpdateTotalCustomers $job) use ($suppliers) {
            $reflectionProperty = new ReflectionProperty(UpdateTotalCustomers::class, 'supplier');
            $reflectionProperty->setAccessible(true);

            /** @var Supplier $supplierInJob */
            $supplierInJob = $reflectionProperty->getValue($job);

            return $suppliers->pluck(Supplier::keyName())->contains($supplierInJob->getKey());
        });
        Bus::assertDispatchedTimes(UpdateCustomersCounter::class, $times);
    }
}
