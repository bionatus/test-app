<?php

namespace App\Actions\Models\User;

use App\Models\Scopes\ByUserId;
use App\Models\User;
use App\User as LegacyUser;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionEvent as NovaActionEvent;
use Laravel\Passport\AuthCode as PassportAuthCode;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Token as PassportToken;
use Laravel\Spark\Announcement as SparkAnnouncement;
use Laravel\Spark\Invitation as SparkInvitation;
use Laravel\Spark\LocalInvoice as SparkLocalInvoice;
use Laravel\Spark\Notification as SparkNotification;
use Laravel\Spark\Subscription as SparkSubscription;
use Laravel\Spark\Token as SparkToken;

class DeleteRelatedUserModels
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute(): void
    {
        $modelsToDeleteManually = Collection::make([
            PassportToken::class,
            PassportAuthCode::class,
            PassportClient::class,
            SparkAnnouncement::class,
            SparkToken::class,
            SparkInvitation::class,
            SparkLocalInvoice::class,
            SparkNotification::class,
            SparkSubscription::class,
        ]);

        $userId = $this->user->getKey();

        $modelsToDeleteManually->each(function($model) use ($userId) {
            $modelToDelete = new $model;
            $modelToDelete->scoped(new ByUserId($userId))->delete();
        });

        $this->deleteNovaActionEvents($userId);

        LegacyUser::find($userId)->roles()->detach();

        $this->user->appNotifications()->delete();
        $this->user->relatedActivity()->delete();
    }

    private function deleteNovaActionEvents(int $userId): void
    {
        NovaActionEvent::scoped(new ByUserId($userId))->delete();
        $types = Collection::make(['actionable', 'target', 'model']);
        $types->each(function(string $type) use ($userId) {
            NovaActionEvent::where($type . '_type', LegacyUser::class)->where($type . '_id', $userId)->delete();
        });
    }
}
