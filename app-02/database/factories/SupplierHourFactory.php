<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierHour;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @method Collection|SupplierHour create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierHour createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierHour make($attributes = [], ?Model $parent = null)
 */
class SupplierHourFactory extends Factory
{
    public function definition(): array
    {
        $time = Carbon::createFromFormat('G', $this->faker->numberBetween(0,22));
        return [
            'supplier_id' => Supplier::factory(),
            'day'         => Str::lower($this->faker->dayOfWeek),
            'from'        => $time->format('H:i'),
            'to'          => $time->addHour()->format('H:i'),
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

    public function monday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'monday',
            ];
        });
    }

    public function tuesday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'tuesday',
            ];
        });
    }

    public function wednesday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'wednesday',
            ];
        });
    }

    public function thursday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'thursday',
            ];
        });
    }

    public function friday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'friday',
            ];
        });
    }

    public function saturday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'saturday',
            ];
        });
    }

    public function sunday(): self
    {
        return $this->state(function() {
            return [
                'day' => 'sunday',
            ];
        });
    }
}
