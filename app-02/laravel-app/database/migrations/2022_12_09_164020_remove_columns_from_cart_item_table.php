<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromCartItemTable extends Migration
{
    const TABLE_NAME = 'cart_item';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['replacement_id']);
            $table->dropColumn('replacement_id', 'generic_part_description');
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('replacement_id');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('generic_part_description');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('replacement_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('generic_part_description')->nullable();
        });
    }
}
