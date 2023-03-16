<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiUsagesTable extends Migration
{
    const TABLE_NAME = 'api_usages';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('supplier_id')->unsigned()->nullable();
            $table->date('date');
            $table->timestamps();
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->index(['user_id', 'date']);
            $table->index(['supplier_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
