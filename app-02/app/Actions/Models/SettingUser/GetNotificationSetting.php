<?php

namespace App\Actions\Models\SettingUser;

use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByUser;
use App\Models\Setting;
use App\Models\User;

class GetNotificationSetting
{
    private User   $user;
    private string $slug;

    public function __construct(User $user, string $slug)
    {
        $this->user = $user;
        $this->slug = $slug;
    }

    public function execute(): bool
    {
        $user         = $this->user;
        $setting      = Setting::scoped(new ByRouteKey($this->slug))->with([
            'settingUsers' => function($query) use ($user) {
                $query->scoped(new ByUser($user));
            },
        ])->first();
        $settingUsers = $setting->settingUsers;

        return $settingUsers->isNotEmpty() ? $settingUsers->first()->value : $setting->value;
    }
}
