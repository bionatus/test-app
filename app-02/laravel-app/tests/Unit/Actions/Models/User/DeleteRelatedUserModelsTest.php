<?php

namespace Tests\Unit\Actions\Models\User;

use App\Actions\Models\User\DeleteRelatedUserModels;
use App\AppNotification;
use App\Models\RelatedActivity;
use App\Models\User;
use App\User as LegacyUser;
use Database\Factories\AppNotificationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\Actions\ActionEvent as NovaActionEvent;
use Laravel\Passport\AuthCode as PassportAuthCode;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Token as PassportToken;
use Laravel\Spark\Announcement as SparkAnnouncement;
use Laravel\Spark\LocalInvoice as SparkLocalInvoice;
use Laravel\Spark\Notification as SparkNotification;
use Laravel\Spark\Subscription as SparkSubscription;
use Laravel\Spark\Token as SparkToken;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteRelatedUserModelsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deletes_the_user_account_with_the_app_notifications_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        $appNotificationFactory = new AppNotificationFactory();
        $appNotificationFactory->usingUser($user)->count(2)->create();
        $appNotificationFactory->usingUser($anotherUser)->count(3)->create();

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new AppNotification())->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new AppNotification())->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_related_activity()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        RelatedActivity::factory()->usingUser($user)->count(2)->create();
        RelatedActivity::factory()->usingUser($anotherUser)->count(3)->create();

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new RelatedActivity())->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new RelatedActivity())->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_roles_associated()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        $role       = Role::create([
            'name'       => 'fake role name',
            'guard_name' => 'api',
        ]);
        $legacyUser = LegacyUser::find($user->getKey());
        $legacyUser->assignRole($role);
        LegacyUser::find($anotherUser->getKey())->assignRole($role);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing('model_has_roles', ['model_id' => $user->getKey()]);
        $this->assertDatabaseHas('model_has_roles', ['model_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_nova_action_events_related()
    {
        $user           = User::factory()->create();
        $anotherUser    = User::factory()->create();
        $yetAnotherUser = User::factory()->create();

        $legacyUser           = LegacyUser::find($user->getKey());
        $legacyAnotherUser    = LegacyUser::find($anotherUser->getKey());
        $legacyYetAnotherUser = LegacyUser::find($yetAnotherUser->getKey());

        NovaActionEvent::forResourceCreate($user, $legacyAnotherUser)->save();
        NovaActionEvent::forResourceCreate($anotherUser, $legacyUser)->save();
        NovaActionEvent::forResourceCreate($anotherUser, $legacyYetAnotherUser)->save();
        NovaActionEvent::forResourceCreate($yetAnotherUser, $legacyAnotherUser)->save();

        (new DeleteRelatedUserModels($user))->execute();

        $table = (new NovaActionEvent())->getTable();

        $this->assertDatabaseMissing($table, ['user_id' => $legacyUser->getKey()]);
        $this->assertDatabaseMissing($table,
            ['actionable_type' => LegacyUser::class, 'actionable_id' => $legacyUser->getKey()]);
        $this->assertDatabaseMissing($table,
            ['target_type' => LegacyUser::class, 'target_id' => $legacyUser->getKey()]);
        $this->assertDatabaseMissing($table, ['model_type' => LegacyUser::class, 'model_id' => $legacyUser->getKey()]);

        $this->assertDatabaseHas($table, ['user_id' => $legacyAnotherUser->getKey()]);
        $this->assertDatabaseHas($table,
            ['actionable_type' => LegacyUser::class, 'actionable_id' => $legacyAnotherUser->getKey()]);
        $this->assertDatabaseHas($table,
            ['target_type' => LegacyUser::class, 'target_id' => $legacyAnotherUser->getKey()]);
        $this->assertDatabaseHas($table,
            ['model_type' => LegacyUser::class, 'model_id' => $legacyAnotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_passport_access_token_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        PassportToken::create([
            'id'        => 1,
            'user_id'   => $user->getKey(),
            'client_id' => 1,
            'revoked'   => false,
        ]);

        PassportToken::create([
            'id'        => 2,
            'user_id'   => $anotherUser->getKey(),
            'client_id' => 2,
            'revoked'   => false,
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new PassportToken)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new PassportToken)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_passport_auth_codes_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        PassportAuthCode::create([
            'id'        => 1,
            'user_id'   => $user->getKey(),
            'client_id' => 1,
            'revoked'   => false,
        ]);

        PassportAuthCode::create([
            'id'        => 2,
            'user_id'   => $anotherUser->getKey(),
            'client_id' => 2,
            'revoked'   => false,
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new PassportAuthCode)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new PassportAuthCode)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_passport_clients_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        PassportClient::create([
            'name'                   => 'fake name user',
            'redirect'               => 'fake redirect user',
            'personal_access_client' => false,
            'password_client'        => true,
            'revoked'                => false,
            'user_id'                => $user->getKey(),
        ]);

        PassportClient::create([
            'name'                   => 'fake name another user',
            'redirect'               => 'fake redirect another user',
            'personal_access_client' => false,
            'password_client'        => true,
            'revoked'                => false,
            'user_id'                => $anotherUser->getKey(),
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new PassportClient)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new PassportClient)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_spark_api_token_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SparkToken::create([
            'id'        => 1,
            'user_id'   => $user->getKey(),
            'name'      => 'fake name',
            'token'     => 'fake token',
            'metadata'  => 'fake metadata',
            'transient' => false,
        ]);

        SparkToken::create([
            'id'        => 2,
            'user_id'   => $anotherUser->getKey(),
            'name'      => 'another user fake name',
            'token'     => 'another user fake token',
            'metadata'  => 'another user fake metadata',
            'transient' => false,
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new SparkToken)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new SparkToken)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_spark_api_announcement_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SparkAnnouncement::create([
            'id'      => 1,
            'user_id' => $user->getKey(),
            'body'    => 'fake body announcement',
        ]);

        SparkAnnouncement::create([
            'id'      => 2,
            'user_id' => $anotherUser->getKey(),
            'body'    => 'another user fake body announcement',
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new SparkAnnouncement)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new SparkAnnouncement)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_spark_api_invoice_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SparkLocalInvoice::create([
            'id'          => 1,
            'user_id'     => $user->getKey(),
            'provider_id' => 1,
        ]);

        SparkLocalInvoice::create([
            'id'          => 2,
            'user_id'     => $anotherUser->getKey(),
            'provider_id' => 1,
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new SparkLocalInvoice)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new SparkLocalInvoice)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_spark_api_notification_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SparkNotification::create([
            'id'      => 1,
            'user_id' => $user->getKey(),
            'body'    => 1,
            'read'    => false,
        ]);

        SparkNotification::create([
            'id'      => 2,
            'user_id' => $anotherUser->getKey(),
            'body'    => 1,
            'read'    => false,
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new SparkNotification)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new SparkNotification)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }

    /** @test */
    public function it_deletes_the_user_account_with_the_spark_api_subscription_related()
    {
        $user        = User::factory()->create();
        $anotherUser = User::factory()->create();

        SparkSubscription::create([
            'id'          => 1,
            'user_id'     => $user->getKey(),
            'name'        => 'fake name',
            'stripe_id'   => 'fake stripe id',
            'stripe_plan' => 'fake stripe plan',
            'quantity'    => 12,
        ]);

        SparkSubscription::create([
            'id'          => 2,
            'user_id'     => $anotherUser->getKey(),
            'name'        => 'another fake name',
            'stripe_id'   => 'another fake stripe id',
            'stripe_plan' => 'another fake stripe plan',
            'quantity'    => 12,
        ]);

        (new DeleteRelatedUserModels($user))->execute();

        $this->assertDatabaseMissing((new SparkSubscription)->getTable(), ['user_id' => $user->getKey()]);
        $this->assertDatabaseHas((new SparkSubscription)->getTable(), ['user_id' => $anotherUser->getKey()]);
    }
}
