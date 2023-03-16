<?php

namespace Database\Factories;

use App\Models\XoxoVoucher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|XoxoVoucher create($attributes = [], ?Model $parent = null)
 * @method Collection|XoxoVoucher make($attributes = [], ?Model $parent = null)
 */
class XoxoVoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'                => $this->faker->numberBetween(),
            'name'                => $this->faker->sentence(),
            'image'               => $this->faker->imageUrl(),
            'value_denominations' => '10,50,100,200',
        ];
    }

    public function unpublished(): self
    {
        return $this->state(function() {
            return [
                'published_at' => null,
            ];
        });
    }

    public function published(): self
    {
        return $this->state(function() {
            return [
                'published_at' => Carbon::now(),
            ];
        });
    }

    public function sort(int $sort = null): self
    {
        $sort = $sort ?: $this->faker->randomDigit;

        return $this->state(function() use ($sort) {
            return [
                'sort' => $sort,
            ];
        });
    }
}
