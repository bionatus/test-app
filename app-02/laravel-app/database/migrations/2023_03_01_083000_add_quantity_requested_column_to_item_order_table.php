<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityRequestedColumnToItemOrderTable extends Migration
{
    const TABLE_NAME = 'item_order';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->integer('quantity_requested')->nullable()->after('quantity');
        });

        DB::statement('UPDATE ' . self::TABLE_NAME . ' SET quantity_requested = quantity');

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->integer('quantity_requested')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('quantity_requested');
        });
    }
}
