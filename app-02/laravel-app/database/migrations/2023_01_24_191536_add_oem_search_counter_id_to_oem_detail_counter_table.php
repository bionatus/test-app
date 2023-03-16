<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOemSearchCounterIdToOemDetailCounterTable extends Migration
{
    const TABLE_NAME = 'oem_detail_counter';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('oem_search_counter_id')
                ->nullable()
                ->after('oem_id')
                ->constrained('oem_search_counter')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['oem_search_counter_id']);
            $table->dropColumn('oem_search_counter_id');
        });
    }
}
