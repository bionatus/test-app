<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullTextIndexToPartsTable extends Migration
{
    const TABLE_NAME = 'parts';
    const SQLITE     = 'sqlite';

    public function up()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropIndex('parts_number_index');
            $table->fullText('number');
        });
    }

    public function down()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropIndex('parts_number_fulltext');
            $table->index('number');
        });
    }
}
