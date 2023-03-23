<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByAirtableId implements Scope
{
    private int $airtableId;

    public function __construct(int $airtableId)
    {
        $this->airtableId = $airtableId;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('airtable_id', $this->airtableId);
    }
}
