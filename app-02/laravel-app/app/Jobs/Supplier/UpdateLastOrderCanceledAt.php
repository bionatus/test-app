<?php

namespace App\Jobs\Supplier;

use App;
use App\Models\Supplier;
use Carbon\Carbon;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLastOrderCanceledAt implements ShouldQueue
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
        $database     = App::make('firebase.database');
        $databaseNode = Config::get('live.firebase.database_node');
        $key          = $databaseNode . $this->supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'last_order_canceled_at';
        $value        = Carbon::now();

        $database->getReference()->update([$key => $value]);
    }
}
