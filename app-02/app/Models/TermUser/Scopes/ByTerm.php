<?php

namespace App\Models\TermUser\Scopes;

use App\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByTerm implements Scope
{
    private Term $term;

    public function __construct(Term $term)
    {
        $this->term = $term;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('term_id', $this->term->getKey());
    }
}
