<?php

namespace App\Observers;

use App\Actions\Models\Activity\BuildProperty;
use App\Events\User\HubspotFieldUpdated;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Phone;
use App\Models\User;
use Auth;

class PhoneObserver
{
    public function created(Phone $phone)
    {
        if ($phone->user) {
            HubspotFieldUpdated::dispatch($phone->user);
        }
    }

    public function updated(Phone $phone)
    {
        $this->dispatchUserHubspotUpdated($phone);
    }

    public function saved(Phone $phone)
    {
        if ($phone->isDirty('number')) {
            $property = (new BuildProperty('number', $phone->number))->execute();
            LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_PROFILE, $phone, Auth::getUser(),
                $property, Activity::TYPE_PROFILE);
        }
    }

    public function deleted(Phone $phone)
    {
        if ($phone->user) {
            HubspotFieldUpdated::dispatch($phone->user);
        }
    }

    private function dispatchUserHubspotUpdated(Phone $phone): void
    {
        if (!$phone->isDirty(['user_id', 'country_code', 'number'])) {
            return;
        }

        if (($user = $phone->user) || (($userId = $phone->getOriginal('user_id')) && ($user = User::find($userId)))) {
            HubspotFieldUpdated::dispatch($user);
        }
    }
}
