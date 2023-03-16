<?php

namespace Database\Factories;

use App\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppNotificationFactory extends Factory
{
    protected $model = AppNotification::class;

    public function definition(): array
    {
        return [
            'schedule_id' => $this->faker->unique()->uuid,
            'push_id'     => $this->faker->unique()->uuid,
            'name'        => $this->faker->text($this->faker->numberBetween(10, 255)),
            'type'        => $this->faker->text($this->faker->numberBetween(10, 255)),
            'message'     => $this->faker->text($this->faker->numberBetween(10, 255)),
            'date'        => Carbon::now('UTC')->subDays($this->faker->numberBetween(1, 5)),
            'tag_name'    => $this->faker->text($this->faker->numberBetween(10, 255)),
            'user_id'     => User::factory(),
            'read'        => 0,
            'created_at'  => Carbon::now('UTC'),
            'updated_at'  => Carbon::now('UTC'),
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

    public function read(): self
    {
        return $this->state(function() {
            return [
                'read' => true,
            ];
        });
    }
}
