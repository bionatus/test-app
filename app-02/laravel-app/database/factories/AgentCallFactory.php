<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|AgentCall create($attributes = [], ?Model $parent = null)
 * @method Collection|AgentCall make($attributes = [], ?Model $parent = null)
 */
class AgentCallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'call_id'  => Call::factory(),
            'status'   => AgentCall::STATUS_RINGING,
        ];
    }

    public function usingAgent(Agent $agent): self
    {
        return $this->state(function() use ($agent) {
            return [
                'agent_id' => $agent,
            ];
        });
    }

    public function usingCall(Call $call): self
    {
        return $this->state(function() use ($call) {
            return [
                'call_id' => $call,
            ];
        });
    }

    public function invalid(): self
    {
        return $this->state(function() {
            return [
                'status' => AgentCall::STATUS_INVALID,
            ];
        });
    }

    public function ringing(): self
    {
        return $this->state(function() {
            return [
                'status' => AgentCall::STATUS_RINGING,
            ];
        });
    }

    public function inProgress(): self
    {
        return $this->state(function() {
            return [
                'status' => AgentCall::STATUS_IN_PROGRESS,
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function() {
            return [
                'status' => AgentCall::STATUS_COMPLETED,
            ];
        });
    }
}
