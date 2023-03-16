<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class SetUsersAccreditatedValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:user-accreditation-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set user accreditation value for older users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereDate('created_at', '<', '2019-10-10')->get();

        if (empty($users)) {
            return;
        }

        foreach ($users as $user) {
            $user->accreditated = true;

            $user->save();
        }
    }
}
