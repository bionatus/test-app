<?php

namespace App\Jobs\Supplier;

use App;
use App\Models\Order;
use App\Models\Order\Scopes\BySupplier;
use App\Models\Order\Scopes\PriceAndAvailabilityRequests;
use App\Models\Order\Scopes\WillCallAndApprovedOrders;
use App\Models\Supplier;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetTotalActiveOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->onConnection('database');

        $this->supplier = $supplier;
    }

    public function handle()
    {
        $supplier    = $this->supplier;
        $totalOrders = Order::query()->scoped(new BySupplier($supplier))->Where(function(Builder $builder) {
            $builder->scoped(new PriceAndAvailabilityRequests())->orWhere(function(Builder $builder) {
                $builder->scoped(new WillCallAndApprovedOrders());
            });
        })->count();

        $database     = App::make('firebase.database');
        $databaseNode = Config::get('live.firebase.supplier_total_order_node');
        $key          = $databaseNode . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'total_orders';

        $database->getReference()->update([$key => $totalOrders]);
    }
}
