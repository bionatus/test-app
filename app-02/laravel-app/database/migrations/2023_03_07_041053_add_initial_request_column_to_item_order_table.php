<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialRequestColumnToItemOrderTable extends Migration
{
    const TABLE_NAME = 'item_order';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->boolean('initial_request')->default(true)->after('status');
            $table->dropForeign(['item_id']);
            $table->dropForeign(['order_id']);
            $table->dropUnique(['item_id', 'order_id']);
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['item_id', 'order_id', 'initial_request']);
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(['item_id']);
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(['order_id']);
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->boolean('initial_request')->default(true)->after('status');
            $table->foreignId('item_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['item_id', 'order_id', 'initial_request']);
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table){
            $table->foreignId('item_id')->nullable(false)->change();
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropForeign(['order_id']);
            $table->dropUnique(['item_id', 'order_id', 'initial_request']);
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dropColumn('initial_request');
            $table->unique(['item_id', 'order_id']);
        });
    }
}
