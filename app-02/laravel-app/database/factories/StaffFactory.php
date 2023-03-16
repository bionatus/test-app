<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|Staff create($attributes = [], ?Model $parent = null)
 * @method Collection|Staff createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Staff make($attributes = [], ?Model $parent = null)
 */
class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id'             => Supplier::factory(),
            'uuid'                    => $this->faker->unique()->uuid,
            'password'                => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'type'                    => Staff::TYPE_OWNER,
            'initial_password_set_at' => Carbon::now(),
            'name'                    => $this->faker->name,
        ];
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }

    public function owner(): self
    {
        return $this->state(function() {
            return [
                'type' => Staff::TYPE_OWNER,
            ];
        });
    }

    public function accountant(): self
    {
        return $this->state(function() {
            return [
                'type' => Staff::TYPE_ACCOUNTANT,
            ];
        });
    }

    public function manager(): self
    {
        return $this->state(function() {
            return [
                'type' => Staff::TYPE_MANAGER,
            ];
        });
    }

    public function counter(): self
    {
        return $this->state(function() {
            return [
                'type' => Staff::TYPE_COUNTER,
            ];
        });
    }

    public function contact(): self
    {
        return $this->state(function() {
            return [
                'type' => Staff::TYPE_CONTACT,
            ];
        });
    }

    public function withEmail(): self
    {
        return $this->state(function() {
            return [
                'email' => $this->faker->email,
            ];
        });
    }
}
