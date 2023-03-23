<?php

namespace App\Models\User\Scopes;

use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\NewestUpdated;
use App\Models\Scopes\Oldest;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WithSupplierRelationships implements Scope
{
    private Supplier $supplier;
    private array    $statuses;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
        $this->statuses = array_merge(Substatus::STATUSES_PENDING, Substatus::STATUSES_PENDING_APPROVAL);
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->with([
            'pubnubChannels' => function($query) {
                $query->scoped(new BySupplier($this->supplier));
            },
            'supplierUsers'  => function($query) {
                $query->scoped(new BySupplier($this->supplier));
            },
            'orders'         => function($query) {
                $query->scoped(new BySupplier($this->supplier))
                    ->scoped(new ByLastSubstatuses(Substatus::STATUSES_PENDING))
                    ->scoped(new Oldest());
            },
            'devices'        => function($query) {
                $query->scoped(new NewestUpdated());
            },
        ])->withExists([
            'orders' => function($query) {
                $query->scoped(new BySupplier($this->supplier))
                    ->scoped(new ByLastSubstatuses($this->statuses))
                    ->whereNotNull('working_on_it');
            },
        ])->withCount([
            'orders as pending_orders_count'          => function($query) {
                $query->scoped(new BySupplier($this->supplier))
                    ->scoped(new ByLastSubstatuses(Substatus::STATUSES_PENDING));
            },
            'orders as pending_approval_orders_count' => function($query) {
                $query->scoped(new BySupplier($this->supplier))
                    ->scoped(new ByLastSubstatuses(Substatus::STATUSES_PENDING_APPROVAL));
            },
        ]);
    }
}
