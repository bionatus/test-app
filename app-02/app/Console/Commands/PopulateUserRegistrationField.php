<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class PopulateUserRegistrationField extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complete:user-registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete all users registration';

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
     * @return int
     */
    public function handle()
    {
        $users = User::whereDate('created_at', '<', '2020-11-05')
            ->where('accreditated', true)
            ->get();

        if (empty($users)) {
            return;
        }

        foreach ($users as $user) {
            $user->registration_completed = true;

            $user->save();
        }

        return 0;
    }
}
