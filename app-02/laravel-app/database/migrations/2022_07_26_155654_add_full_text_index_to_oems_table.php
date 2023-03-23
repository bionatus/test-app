<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullTextIndexToOemsTable extends Migration
{
    const TABLE_NAME = 'oems';
    const SQLITE     = 'sqlite';

    public function up()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropIndex('oems_model_index');
            $table->fullText('model');
        });
    }

    public function down()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropIndex('oems_model_fulltext');
            $table->index('model');
        });
    }
}
