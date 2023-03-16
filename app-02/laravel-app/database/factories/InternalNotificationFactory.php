<?php

namespace Database\Factories;

use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|InternalNotification create($attributes = [], ?Model $parent = null)
 * @method Collection|InternalNotification make($attributes = [], ?Model $parent = null)
 */
class InternalNotificationFactory extends Factory
{
    public function definition(): array
    {
        $source = Post::factory()->create();

        return [
            'user_id'      => User::factory(),
            'uuid'         => $this->faker->unique()->uuid,
            'message'      => $this->faker->text($this->faker->numberBetween(10, 100)),
            'read_at'      => null,
            'source_event' => PushNotificationEventNames::CREATED,
            'source_type'  => Post::MORPH_ALIAS,
            'source_id'    => $source->getRouteKey(),
            'data'         => null,
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingSource(Model $model): self
    {
        return $this->state(function() use ($model) {
            return [
                'source_type' => $model->getMorphClass(),
                'source_id'   => $model->getRouteKey(),
            ];
        });
    }

    public function read(): self
    {
        return $this->state(function() {
            return [
                'read_at' => Carbon::now(),
            ];
        });
    }
}
