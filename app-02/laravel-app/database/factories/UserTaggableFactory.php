<?php

namespace Database\Factories;

use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Series;
use App\Models\System;
use App\Models\User;
use App\Models\UserTaggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @method Collection|UserTaggable create($attributes = [], ?Model $parent = null)
 * @method Collection|UserTaggable make($attributes = [], ?Model $parent = null)
 */
class UserTaggableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'taggable_type' => function() {
                return Relation::getAliasByModel(PlainTag::class);
            },
            'taggable_id'   => function() {
                return PlainTag::factory();
            },
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function series(): self
    {
        return $this->state(function() {
            $series = Series::factory()->create();

            return [
                'taggable_id'   => $series,
                'taggable_type' => Relation::getAliasByModel(get_class($series)),
            ];
        });
    }

    public function modelType(): self
    {
        return $this->state(function() {
            $modelType = ModelType::factory()->create();

            return [
                'taggable_id'   => $modelType,
                'taggable_type' => Relation::getAliasByModel(get_class($modelType)),
            ];
        });
    }

    public function plainTag(): self
    {
        return $this->state(function() {
            $plainTag = PlainTag::factory()->create();

            return [
                'taggable_id'   => $plainTag,
                'taggable_type' => Relation::getAliasByModel(get_class($plainTag)),
            ];
        });
    }

    public function general(): self
    {
        return $this->state(function() {
            $plainTag = PlainTag::factory()->general()->create();

            return [
                'taggable_id'   => $plainTag,
                'taggable_type' => Relation::getAliasByModel(get_class($plainTag)),
            ];
        });
    }

    public function issue(): self
    {
        return $this->state(function() {
            $plainTag = PlainTag::factory()->issue()->create();

            return [
                'taggable_id'   => $plainTag,
                'taggable_type' => Relation::getAliasByModel(get_class($plainTag)),
            ];
        });
    }

    public function more(): self
    {
        return $this->state(function() {
            $plainTag = PlainTag::factory()->more()->create();

            return [
                'taggable_id'   => $plainTag,
                'taggable_type' => Relation::getAliasByModel(get_class($plainTag)),
            ];
        });
    }

    public function usingSeries(Series $series): self
    {
        return $this->state(function() use ($series) {
            return [
                'taggable_id'   => $series,
                'taggable_type' => Relation::getAliasByModel(get_class($series)),
            ];
        });
    }

    public function usingPlainTag(PlainTag $plainTag): self
    {
        return $this->state(function() use ($plainTag) {
            return [
                'taggable_id'   => $plainTag,
                'taggable_type' => Relation::getAliasByModel(get_class($plainTag)),
            ];
        });
    }

    public function usingModelType(ModelType $modelType): self
    {
        return $this->state(function() use ($modelType) {
            return [
                'taggable_id'   => $modelType,
                'taggable_type' => Relation::getAliasByModel(get_class($modelType)),
            ];
        });
    }
}
