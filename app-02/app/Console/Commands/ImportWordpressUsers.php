<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use App\User;

class ImportWordpressUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:wordpress-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Users from WordPress Users table as CSV';

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
        $file = fopen('files/bluonenergy_com_6_users.csv', 'r');

        while (($filedata = fgetcsv($file, 1000, ',')) !== false) {
            $user = User::firstOrNew(['legacy_id' => $filedata[0]]);

            $user->password = bcrypt(str_random(10));
            $user->user_login = $filedata[1];
            $user->legacy_password = $filedata[2];
            $user->email = $filedata[4];
            $user->created_at = $filedata[6];
            $user->name = $filedata[9];

            $user->save();
        }
    }
}
