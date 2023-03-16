<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\RelatedActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|RelatedActivity create($attributes = [], ?Model $parent = null)
 * @method Collection|RelatedActivity make($attributes = [], ?Model $parent = null)
 */
class RelatedActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'user_id'     => User::factory(),
            'resource'    => Activity::RESOURCE_POST,
            'event'       => Activity::ACTION_REPLIED,
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user->getKey(),
            ];
        });
    }

    public function usingActivity(Activity $activity): self
    {
        return $this->state(function() use ($activity) {
            return [
                'activity_id' => $activity->getKey(),
            ];
        });
    }
}
