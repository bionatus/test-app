<?php

namespace App\Models\OrderInvoice\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCreatedBetween implements Scope
{
    private string $fromDate;
    private string $tillDate;

    public function __construct(string $fromDate, string $tillDate)
    {
        $this->fromDate = $fromDate;
        $this->tillDate = $tillDate;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereBetween('created_at', [$this->fromDate, $this->tillDate]);
    }
}
