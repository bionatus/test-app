<?php

namespace App\Models;

use Database\Factories\AppVersionFactory;
use Illuminate\Support\Carbon;
use Lang;

/**
 * @method static AppVersionFactory factory()
 *
 * @mixin AppVersion
 */
class AppVersion extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */

    public function needsConfirm(string $version, User $user): bool
    {
        $currentTime = Carbon::now();
        $flag        = Lang::get(Flag::APP_VERSION_CONFIRM, ['app_version' => $this->current]);

        $maxDateProcessed = (!$user->registration_completed_at && !$user->verified_at) ? $user->created_at : max($user->registration_completed_at,
            $user->verified_at);

        return version_compare($version, $this->current,
                '==') && !$user->hasFlag($flag) && !empty($this->video_url) && ($currentTime->subDay() >= $maxDateProcessed);
    }

    public function needsUpdate(string $version): bool
    {
        return version_compare($version, $this->min, '<');
    }

    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
