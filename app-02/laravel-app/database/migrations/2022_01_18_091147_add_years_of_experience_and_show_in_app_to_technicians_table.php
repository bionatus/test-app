<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearsOfExperienceAndShowInAppToTechniciansTable extends Migration
{
    const TABLE_NAME = 'technicians';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->integer('years_of_experience')->nullable();
            $table->boolean('show_in_app')->default(true);
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('years_of_experience');
            $table->dropColumn('show_in_app');
        });
    }
}
