<?php

namespace App\Models\Supplier\Scopes;

use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\Scopes\ByUser;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByOrdersAndSupplierUsersWithoutOrdersInUser implements Scope
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $orderTableName         = Order::tableName();
        $pubnubChannelTableName = PubnubChannel::tableName();
        $supplierTableName      = Supplier::tableName();

        $suppliersWithSupplierUsersAndWithoutOrders = $model->selectRaw("$supplierTableName.*, NULL as {$orderTableName}_updated_at, $pubnubChannelTableName.created_at as {$pubnubChannelTableName}_created_at")
            ->leftJoin($pubnubChannelTableName, 'supplier_id', "$supplierTableName.id")
            ->where("$pubnubChannelTableName.user_id", $this->user->getKey())
            ->whereNotNull("$supplierTableName.published_at")
            ->whereHas('supplierUsers', function(Builder $query) {
                $query->scoped(new ByUser($this->user));
            })
            ->whereDoesntHave('orders', function(Builder $query) {
                $query->scoped(new ByUser($this->user));
            });

        $builder->selectRaw("$supplierTableName.*, max($orderTableName.updated_at) as {$orderTableName}_updated_at, NULL as {$pubnubChannelTableName}_created_at")
            ->join($orderTableName, 'supplier_id', "$supplierTableName.id")
            ->where("$orderTableName.user_id", $this->user->getKey())
            ->groupBy("$supplierTableName.id")
            ->union($suppliersWithSupplierUsersAndWithoutOrders)
            ->orderByRaw("{$orderTableName}_updated_at desc, {$pubnubChannelTableName}_created_at desc");
    }
}
