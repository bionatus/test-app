<?php

use Database\Seeders\DeleteBrandSeeder;
use Illuminate\Database\Migrations\Migration;

class DeleteBrandTags extends Migration
{
    public function up()
    {
        Artisan::call('db:seed', ['--class' => DeleteBrandSeeder::class]);
    }

    public function down()
    {
        //
    }
}
