<?php

namespace Database\Factories;

use App\Models\Communication;
use App\Models\CommunicationLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CommunicationLog create($attributes = [], ?Model $parent = null)
 * @method Collection|CommunicationLog make($attributes = [], ?Model $parent = null)
 */
class CommunicationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'communication_id' => Communication::factory(),
            'request'          => [],
            'response'         => '',
            'errors'           => [],
        ];
    }

    public function usingCommunication(Communication $communication): self
    {
        return $this->state(function() use ($communication) {
            return [
                'communication_id' => $communication,
            ];
        });
    }
}
