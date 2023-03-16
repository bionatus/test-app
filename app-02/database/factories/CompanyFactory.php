<?php

namespace Database\Factories;

use App\Models\Company;
use App\Types\CompanyDataType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method Collection|Company create($attributes = [], ?Model $parent = null)
 * @method Collection|Company createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Company make($attributes = [], ?Model $parent = null)
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'     => $this->faker->uuid,
            'name'     => $this->faker->company,
            'type'     => $this->faker->randomElement([
                CompanyDataType::TYPE_CONTRACTOR,
                CompanyDataType::TYPE_SUPPLY_HOUSE,
                CompanyDataType::TYPE_TRADE_SCHOOL,
                CompanyDataType::TYPE_OEM,
                CompanyDataType::TYPE_PROPERTY_MANAGER_OWNER,
            ]),
            'country'  => $this->faker->country,
            'state'    => $this->faker->state,
            'city'     => $this->faker->city,
            'zip_code' => $this->faker->postcode,
            'address'  => $this->faker->address,
        ];
    }
}
