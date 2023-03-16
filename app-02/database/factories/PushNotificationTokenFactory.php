<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\PushNotificationToken;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PushNotificationToken create($attributes = [], ?Model $parent = null)
 * @method Collection|PushNotificationToken make($attributes = [], ?Model $parent = null)
 */
class PushNotificationTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'      => $this->faker->unique()->uuid,
            'os'        => PushNotificationToken::OS_ANDROID,
            'device_id' => Device::factory(),
            'token'     => $this->faker->word,
        ];
    }

    public function usingDevice(Device $device): self
    {
        return $this->state(function() use ($device) {
            return [
                'device_id' => $device,
            ];
        });
    }
}
