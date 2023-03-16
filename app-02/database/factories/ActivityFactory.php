<?php

namespace Database\Factories;

use App\Actions\Models\Activity\Contracts\Executable;
use App\Models\Activity;
use App\Models\Model;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use ReflectionClass;

/**
 * @method Collection|Activity create($attributes = [], ?Model $parent = null)
 * @method Collection|Activity make($attributes = [], ?Model $parent = null)
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'log_name'     => Activity::TYPE_FORUM,
            'description'  => Activity::ACTION_CREATED . '.' . Activity::RESOURCE_POST,
            'subject_type' => Post::MORPH_ALIAS,
            'subject_id'   => Post::factory(),
            'causer_type'  => User::MORPH_ALIAS,
            'causer_id'    => User::factory(),
            'properties'   => [
                'id'   => $this->faker->uuid,
                'user' => [
                    'id'         => $user->getRouteKey(),
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                ],
            ],
            'resource'     => Activity::RESOURCE_POST,
            'event'        => Activity::ACTION_CREATED,
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ];
    }

    public function forum()
    {
        return $this->state(function() {
            return [
                'log_name' => Activity::TYPE_FORUM,
            ];
        });
    }

    public function orders()
    {
        return $this->state(function() {
            return [
                'log_name' => Activity::TYPE_ORDER,
            ];
        });
    }

    public function usingSubject(Model $subject, string $resourceType = null): self
    {
        return $this->state(function(array $attributes) use ($subject, $resourceType) {
            $reflection   = new ReflectionClass($subject);
            $itemClass    = strtolower($reflection->getShortName());
            $resourceType = $resourceType ?? $itemClass;
            $properties   = [];

            return [
                'subject_type' => $itemClass,
                'subject_id'   => $subject->getKey(),
                'resource'     => $resourceType,
                'description'  => $attributes['event'] . '.' . $resourceType,
                'properties'   => $properties,
            ];
        });
    }

    public function usingEvent(string $event): self
    {
        return $this->state(function(array $attributes) use ($event) {
            return [
                'event'       => $event,
                'description' => $event . '.' . $attributes['resource'],
            ];
        });
    }

    public function usingResource(Executable $resource, Model $model): self
    {
        return $this->state(function() use ($resource, $model) {
            return [
                'properties' => $resource->execute(),
            ];
        });
    }

    public function usingCauser(User $causer): self
    {
        return $this->state(function() use ($causer) {
            return [
                'causer_type' => User::MORPH_ALIAS,
                'causer_id'   => $causer->getKey(),
            ];
        });
    }
}
