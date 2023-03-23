<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartSearchCounterIdToPartDetailCounterTable extends Migration
{
    const TABLE_NAME = 'part_detail_counter';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('part_search_counter_id')
                ->nullable()
                ->after('part_id')
                ->constrained('part_search_counter')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['part_search_counter_id']);
            $table->dropColumn('part_search_counter_id');
        });
    }
}
