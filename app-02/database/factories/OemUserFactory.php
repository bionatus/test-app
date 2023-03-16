<?php

namespace Database\Factories;

use App\Models\Oem;
use App\Models\OemUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OemUser create($attributes = [], ?Model $parent = null)
 * @method Collection|OemUser createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|OemUser make($attributes = [], ?Model $parent = null)
 */
class OemUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'oem_id'  => Oem::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function usingOem(Oem $oem): self
    {
        return $this->state(function() use ($oem) {
            return [
                'oem_id' => $oem,
            ];
        });
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }
}
