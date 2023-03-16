<?php

namespace App\Observers;

use App;
use App\Actions\Models\Activity\BuildProperty;
use App\Actions\Models\User\DeleteRelatedUserModels;
use App\Actions\Models\User\GetTimezone;
use App\Actions\Models\User\ProcessOrdersFromDeletedUser;
use App\Events\User\HubspotFieldUpdated;
use App\Jobs\LogActivity;
use App\Jobs\Supplier\UpdateCustomersCounter;
use App\Jobs\Supplier\UpdateTotalCustomers;
use App\Jobs\User\DeleteFirebaseNode;
use App\Models\Activity;
use App\Models\Supplier;
use App\Models\User;
use Auth;

class UserObserver
{
    const AttributesToLog = [
        'first_name',
        'last_name',
        'public_name',
        'photo',
        'bio',
        'address',
        'address_2',
        'country',
        'city',
        'state',
        'zip',
    ];

    public function updating(User $user)
    {
        if (null === $user->public_name) {
            $user->generateSlug();
        }

        if (!$user->isDirty('verified_at')) {
            $user->verify();
        }
    }

    public function updated(User $user)
    {
        if ($user->requiresHubspotSync()) {
            HubspotFieldUpdated::dispatch($user);
        }
        foreach (UserObserver::AttributesToLog as $attribute) {
            if ($user->isDirty($attribute)) {
                $this->logActivity($user, $attribute, $user->$attribute);
            }
        }
    }

    public function deleting(User $user)
    {
        (App::make(ProcessOrdersFromDeletedUser::class, ['user' => $user]))->execute();
        (App::make(DeleteRelatedUserModels::class, ['user' => $user]))->execute();
        DeleteFirebaseNode::dispatch($user);

        $user->suppliers()->cursor()->each(function(Supplier $supplier) {
            UpdateCustomersCounter::dispatch($supplier);
            UpdateTotalCustomers::dispatch($supplier);
        });
    }

    public function saving(User $user)
    {
        if ($user->isDirty(['country', 'state', 'zip'])) {
            $user->timezone = (App::make(GetTimezone::class, ['user' => $user]))->execute();
        }
    }

    private function logActivity($user, $key, $value)
    {
        $property = (new BuildProperty($key, $value))->execute();
        LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_PROFILE, $user, Auth::getUser(), $property,
            Activity::TYPE_PROFILE);
    }
}
