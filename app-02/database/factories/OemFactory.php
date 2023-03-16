<?php

namespace Database\Factories;

use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Oem create($attributes = [], ?Model $parent = null)
 * @method Collection|Oem make($attributes = [], ?Model $parent = null)
 */
class OemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'          => $this->faker->unique()->uuid,
            'series_id'     => Series::factory(),
            'model_type_id' => ModelType::factory(),
            'model'         => $this->faker->text(),
            'logo'          => $this->faker->word,
            'status'        => Oem::STATUS_LIVE,
        ];
    }

    public function usingSeries(Series $series): self
    {
        return $this->state(function() use ($series) {
            return [
                'series_id' => $series,
            ];
        });
    }

    public function usingModelType(ModelType $modelType): self
    {
        return $this->state(function() use ($modelType) {
            return [
                'model_type_id' => $modelType,
            ];
        });
    }

    public function live(): self
    {
        return $this->state(function() {
            return [
                'status' => Oem::STATUS_LIVE,
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function() {
            return [
                'status' => Oem::STATUS_PENDING,
            ];
        });
    }
}
