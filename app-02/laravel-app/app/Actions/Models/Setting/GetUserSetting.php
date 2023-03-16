<?php

namespace App\Actions\Models\Setting;

use App\Models\Scopes\ByUser;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserSetting extends GetSetting
{
    protected function completeSettingQuery()
    {
        /** @var User $user */
        $user = $this->model;
        $this->settingQuery->with([
            'settingUsers' => function($query) use ($user) {
                $query->scoped(new ByUser($user));
            },
        ]);
    }

    protected function getRelationship(Setting $setting): Collection
    {
        return $setting->settingUsers;
    }
}
