<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOemIdAndStaffIdColumnsInOrdersTable extends Migration
{
    const TABLE_NAME = 'orders';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['oem_id']);
            $table->foreign('staff_id')->references('id')->on('staff')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('oem_id')->references('id')->on('oems')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down()
    {
        if ('sqlite' === DB::connection()->getName()) {
            return;
        }
        
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['oem_id']);
            $table->foreign('staff_id')->references('id')->on('staff')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('oem_id')->references('id')->on('oems')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
}
