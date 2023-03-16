<?php

namespace App\Jobs\Supplier;

use App;
use App\Models\Supplier;
use App\Models\User\Scopes\ByEnabled;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTotalCustomers implements ShouldQueue
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
        $key          = $databaseNode . $this->supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'total_customers';
        $value        = $this->supplier->users()->scoped(new ByEnabled())->count();
        $database->getReference()->update([$key => $value]);
    }
}
