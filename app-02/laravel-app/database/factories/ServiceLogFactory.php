<?php

namespace Database\Factories;

use App\Models\ServiceLog;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Collection|ServiceLog create($attributes = [], ?Model $parent = null)
 * @method Collection|ServiceLog make($attributes = [], ?Model $parent = null)
 */
class ServiceLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'             => $this->faker->text(),
            'request_method'   => Request::METHOD_GET,
            'request_url'      => $this->faker->url,
            'response_status'  => Response::HTTP_OK,
            'response_content' => $this->faker->text(),
        ];
    }

    public function get(): ServiceLogFactory
    {
        return $this->state(function() {
            return [
                'request_method' => Request::METHOD_GET,
            ];
        });
    }

    public function post(): ServiceLogFactory
    {
        return $this->state(function() {
            return [
                'request_method' => Request::METHOD_POST,
            ];
        });
    }

    public function put(): ServiceLogFactory
    {
        return $this->state(function() {
            return [
                'request_method' => Request::METHOD_PUT,
            ];
        });
    }

    public function delete(): ServiceLogFactory
    {
        return $this->state(function() {
            return [
                'request_method' => Request::METHOD_DELETE,
            ];
        });
    }

    public function usingUser(User $user): ServiceLogFactory
    {
        return $this->state(function() use ($user) {
            return [
                'causer_type' => Relation::getAliasByModel(User::class),
                'causer_id'   => $user->getKey(),
            ];
        });
    }

    public function usingSupplier(Supplier $supplier): ServiceLogFactory
    {
        return $this->state(function() use ($supplier) {
            return [
                'causer_type' => Relation::getAliasByModel(Supplier::class),
                'causer_id'   => $supplier->getKey(),
            ];
        });
    }
}
