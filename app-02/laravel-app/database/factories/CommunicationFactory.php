<?php

namespace Database\Factories;

use App\Models\Communication;
use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Communication create($attributes = [], ?Model $parent = null)
 * @method Collection|Communication make($attributes = [], ?Model $parent = null)
 */
class CommunicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id'  => Session::factory(),
            'uuid'        => $this->faker->unique()->uuid,
            'provider'    => Communication::PROVIDER_TWILIO,
            'provider_id' => 'CA' . $this->faker->unique()->password(32, 32),
            'channel'     => Communication::CHANNEL_CALL,
        ];
    }

    public function usingSession(Session $session): self
    {
        return $this->state(function() use ($session) {
            return [
                'session_id' => $session,
            ];
        });
    }

    public function call(): self
    {
        return $this->state(function() {
            return [
                'channel' => Communication::CHANNEL_CALL,
            ];
        });
    }

    public function chat(): self
    {
        return $this->state(function() {
            return [
                'channel' => Communication::CHANNEL_CHAT,
            ];
        });
    }
}
