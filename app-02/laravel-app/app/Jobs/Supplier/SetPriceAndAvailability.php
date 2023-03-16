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

class SetPriceAndAvailability implements ShouldQueue
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
        $updatedDate = Carbon::now();

        $database     = App::make('firebase.database');
        $databaseNode = Config::get('live.firebase.supplier_notification_sound_node');
        $key          = $databaseNode . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'price_and_availability';

        $database->getReference()->update([$key => $updatedDate]);
    }
}
