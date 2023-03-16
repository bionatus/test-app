<?php

namespace App\Jobs\Supplier;

use App;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Substatus;
use App\Models\Supplier;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOutboundCounter implements ShouldQueue
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
        $key          = $databaseNode . $this->supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'outbound';
        $value        = $this->supplier->orders()->scoped(new ByLastSubstatuses(Substatus::STATUSES_APPROVED))->count();

        $database->getReference()->update([$key => $value]);
    }
}
