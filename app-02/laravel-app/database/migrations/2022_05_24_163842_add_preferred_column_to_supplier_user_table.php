<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreferredColumnToSupplierUserTable extends Migration
{
    const TABLE_NAME = 'supplier_user';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->boolean('preferred')->nullable();
            $table->unique(['user_id', 'preferred']);
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'preferred']);
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->dropColumn('preferred');
        });
    }
}
