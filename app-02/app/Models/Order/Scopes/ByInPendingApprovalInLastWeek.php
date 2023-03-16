<?php

namespace App\Models\Order\Scopes;

use App\Models\OrderSubstatus\Scopes\ByLastOfOrder;
use App\Models\OrderSubstatus\Scopes\BySubstatuses;
use App\Models\Scopes\ByCreatedAfter;
use App\Models\Scopes\ByCreatedBefore;
use App\Models\Substatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Carbon;

class ByInPendingApprovalInLastWeek implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('orderSubstatuses', function(Builder $builder) {
            $builder->scoped(new BySubstatuses([
                Substatus::STATUS_PENDING_APPROVAL_FULFILLED,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
            ]))->scoped(new ByCreatedBefore(Carbon::now()->subHour()))->scoped(new ByCreatedAfter(Carbon::now()
                ->subWeek()))->scoped(new ByLastOfOrder("`orders`.`id`"));
        });
    }
}
