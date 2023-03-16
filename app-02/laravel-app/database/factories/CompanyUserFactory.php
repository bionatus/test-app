<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method Collection|CompanyUser create($attributes = [], ?Model $parent = null)
 * @method Collection|CompanyUser make($attributes = [], ?Model $parent = null)
 */
class CompanyUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id'    => User::factory(),
            'job_title'  => $this->faker->jobTitle,
        ];
    }

    public function usingCompany(Company $company): self
    {
        return $this->state(function() use ($company) {
            return [
                'company_id' => $company,
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
