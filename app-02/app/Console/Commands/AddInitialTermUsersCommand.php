<?php

namespace App\Console\Commands;

use App;
use App\Actions\Models\Term\GetCurrentTerm;
use App\Models\User;
use Illuminate\Console\Command;

class AddInitialTermUsersCommand extends Command
{
    protected $signature   = 'tos:add-initial-term';
    protected $description = 'Add initial Term to Users';

    public function handle()
    {
        $currentTerm = App::make(GetCurrentTerm::class)->execute();
        $userIds     = User::select('id')->where('terms', true)->whereDoesntHave('termUsers')->get()->pluck('id');

        $currentTerm->users()->attach($userIds);
    }
}
