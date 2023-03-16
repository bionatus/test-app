<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateItemIdInItemOrderTable extends Migration
{
    const TABLE_NAME = 'item_order';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreignId('item_id')->change()->constrained()->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreignId('item_id')->change()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
}
