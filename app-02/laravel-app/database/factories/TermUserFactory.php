<?php

namespace Database\Factories;

use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|TermUser create($attributes = [], ?Model $parent = null)
 * @method Collection|TermUser createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|TermUser make($attributes = [], ?Model $parent = null)
 */
class TermUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'term_id' => Term::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function usingTerm(Term $term): self
    {
        return $this->state(function() use ($term) {
            return [
                'term_id' => $term,
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
