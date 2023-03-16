<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversionJobsTable extends Migration
{
    public function up()
    {
        Schema::create('conversion_jobs', function(Blueprint $table) {
            $table->increments('id');
            $table->string('control')->index()->unique();
            $table->text('standard')->nullable();
            $table->text('optional')->nullable();
            $table->boolean('yellow_if_standard');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversion_jobs');
    }
}
