<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceLogsTable extends Migration
{
    const TABLE_NAME = 'service_logs';

    public function up()
    {
        Schema::connection(Config::get('database.default_stats'))->create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->nullableMorphs('causer');
            $table->string('name');
            $table->string('request_method');
            $table->string('request_url');
            $table->json('request_payload')->nullable();
            $table->unsignedSmallInteger('response_status');
            $table->mediumText('response_content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection(Config::get('database.default_stats'))->dropIfExists(self::TABLE_NAME);
    }
}
