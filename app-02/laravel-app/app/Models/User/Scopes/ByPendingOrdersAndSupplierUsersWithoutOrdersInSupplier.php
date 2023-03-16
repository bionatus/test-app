<?php

namespace App\Models\User\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\PubnubChannel;
use App\Models\Scopes\ByName;
use App\Models\Scopes\BySupplier;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier implements Scope
{
    private array    $statuses;
    private Supplier $supplier;
    private ?string  $searchString;

    public function __construct(Supplier $supplier, string $searchString = null)
    {
        $this->statuses     = array_merge(Substatus::STATUSES_PENDING, Substatus::STATUSES_PENDING_APPROVAL);
        $this->supplier     = $supplier;
        $this->searchString = $searchString;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $orderTableName         = Order::tableName();
        $pubnubChannelTableName = PubnubChannel::tableName();
        $userTableName          = User::tableName();

        $selectRaw = "$userTableName.*, $pubnubChannelTableName.updated_at as {$pubnubChannelTableName}_updated_at";
        $selectRaw .= ", GREATEST(COALESCE($pubnubChannelTableName.supplier_last_message_at, 0), COALESCE($pubnubChannelTableName.user_last_message_at, 0)) AS last_message_at";

        $usersWithSupplierUsersAndWithoutOrdersInSupplier = $model->selectRaw($selectRaw)
            ->leftJoin($pubnubChannelTableName, function($join) use ($userTableName) {
                $join->on('user_id', "$userTableName.id")->where('supplier_id', $this->supplier->getKey());
            })
            ->whereHas('supplierUsers', function(Builder $query) {
                $query->scoped(new BySupplier($this->supplier));
            })
            ->whereDoesntHave($orderTableName, function(Builder $query) {
                $query->scoped(new BySupplier($this->supplier))->scoped(new ByLastSubstatuses($this->statuses));
            })
            ->scoped(new WithSupplierRelationships($this->supplier));

        $builder->selectRaw($selectRaw)->leftJoin($pubnubChannelTableName, function($join) use ($userTableName) {
            $join->on('user_id', "$userTableName.id")->where('supplier_id', $this->supplier->getKey());
        })->whereHas($orderTableName, function(Builder $query) {
            $query->scoped(new BySupplier($this->supplier))->scoped(new ByLastSubstatuses($this->statuses));
        })->scoped(new WithSupplierRelationships($this->supplier));

        if (!is_null($this->searchString)) {
            $usersWithSupplierUsersAndWithoutOrdersInSupplier->where(function(Builder $query) {
                $query->scoped(new ByFullName($this->searchString));
                $query->orWhereHas('company', function(Builder $query) {
                    $query->scoped(new ByName($this->searchString));
                });
            });

            $builder->where(function(Builder $query) {
                $query->scoped(new ByFullName($this->searchString));
                $query->orWhereHas('company', function(Builder $query) {
                    $query->scoped(new ByName($this->searchString));
                });
            });
        }

        //@NOTE the union should be after adding the search by searchString
        $builder->union($usersWithSupplierUsersAndWithoutOrdersInSupplier)
            ->orderByDesc('last_message_at')
            ->orderByDesc("{$pubnubChannelTableName}_updated_at")
            ->orderByDesc('id');
    }
}
