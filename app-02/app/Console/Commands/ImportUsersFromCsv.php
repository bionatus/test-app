<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\User;

class ImportUsersFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Users from CSV file';

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
        $file = fopen(storage_path('files/users.csv'), 'r');

        while (($filedata = fgetcsv($file, 1000, ',')) !== false) {

            $checkUser = User::where('email', $filedata[0])->first();

            if ($checkUser) {
                continue;
            }

            $user = User::withoutEvents(function() use ($filedata) {
                return new User([
                    'email' => $filedata[0],
                    'first_name' => $filedata[1],
                    'last_name' => $filedata[2],
                    'registration_completed' => true,
                    'registration_completed_at' => $filedata[4],
                    'password' => Hash::make($filedata[5]),
                ]);
            });

            $user->save();
        }

        return 0;
    }
}
