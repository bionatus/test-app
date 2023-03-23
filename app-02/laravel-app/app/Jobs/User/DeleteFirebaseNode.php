<?php

namespace App\Jobs\User;

use App;
use App\Models\User;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteFirebaseNode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $userId;

    public function __construct(User $user)
    {
        $this->onConnection('database');
        $this->userId = $user->getKey();
    }

    public function handle()
    {
        $database     = App::make('firebase.database');
        $databaseNode = Config::get('mobile.firebase.database_node');
        $key          = $databaseNode . $this->userId;

        $database->getReference($key)->remove();
    }
}
