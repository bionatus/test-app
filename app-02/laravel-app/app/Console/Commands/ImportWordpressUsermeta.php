<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use App\User;

class ImportWordpressUsermeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:wordpress-usermeta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Usermeta table from CSV file';

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

        $role_field = 'bluonenergy_com_6_capabilities';

        $roles_map = [
            'administrator' => 'administrator',
            'crb_contractor' => 'contractor',
            'subscriber' => 'subscriber',
            'contractor' => 'contractor',
        ];

        $meta_fields_map = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            $role_field => 'role',
            '_crb_phone' => 'phone',
            '_crb_company' => 'company',
            '_crb_supplier' => 'hvac_supplier',
            '_crb_i-am-a' => 'occupation',
            '_crb_we-mostly-do' => 'type_of_services',
            '_crb_hear-from' => 'references',
            '_crb_mailing-address' => 'address',
            '_crb_city' => 'city',
            '_crb_state' => 'state',
            '_crb_post_code' => 'zip',
            '_crb_country' => 'country',
        ];

        $file = fopen('files/bluonenergy_com_6_usermeta.csv', 'r');

        while (($filedata = fgetcsv($file, 1000, ',')) !== false) {
            $value = '';
            $user = User::firstOrNew(['legacy_id' => $filedata[1]]);

            if (!$user->exists) {
                continue;
            }

            if (!isset($meta_fields_map[ $filedata[2] ]) || empty($filedata[3])) {
                continue;
            }

            if ($filedata[2] === $role_field ) {
                if ( !empty(unserialize($filedata[3]))) {
                    $value = array_keys(unserialize($filedata[3]))[0];
                    $value = $roles_map[$value];

                    if ($value !== 'subscriber') {
                        $user->assignRole($value);
                    }
                }
            } else {
                $value = $filedata[3];
            }

            $user->update([$meta_fields_map[ $filedata[2] ] => $value]);
        }
    }
}
