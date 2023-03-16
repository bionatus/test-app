<?php

namespace App\Models\CurriDelivery\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByBookId implements Scope
{
    private string $bookId;

    public function __construct(string $bookId)
    {
        $this->bookId = $bookId;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('book_id', '=', $this->bookId);
    }
}
