<?php

namespace Database\Factories;

use App\Models\Instrument;
use App\Models\InstrumentSupportCallCategory;
use App\Models\SupportCallCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|InstrumentSupportCallCategory create($attributes = [], ?Model $parent = null)
 * @method Collection|InstrumentSupportCallCategory make($attributes = [], ?Model $parent = null)
 */
class InstrumentSupportCallCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'instrument_id'            => Instrument::factory(),
            'support_call_category_id' => SupportCallCategory::factory(),
        ];
    }

    public function usingSupportCallCategory(SupportCallCategory $category): self
    {
        return $this->state(function() use ($category) {
            return [
                'support_call_category_id' => $category,
            ];
        });
    }

    public function usingInstrument(Instrument $instrument): self
    {
        return $this->state(function() use ($instrument) {
            return [
                'instrument_id' => $instrument,
            ];
        });
    }
}
