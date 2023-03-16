<?php

namespace App\Jobs\User;

use App;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Substatus;
use App\Models\User;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdatePendingApprovalOrdersCounter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user)
    {
        $this->onConnection('database');
        $this->user = $user;
    }

    public function handle()
    {
        $database     = App::make('firebase.database');
        $databaseNode = Config::get('mobile.firebase.database_node');
        $key          = $databaseNode . $this->user->getKey() . DIRECTORY_SEPARATOR . 'pending_approval_orders';
        $value        = $this->user->orders()
            ->scoped(new ByLastSubstatuses(Substatus::STATUSES_PENDING_APPROVAL))
            ->count();

        $database->getReference()->update([$key => $value]);
    }
}
