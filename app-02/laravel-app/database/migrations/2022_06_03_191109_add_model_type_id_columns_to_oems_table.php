<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModelTypeIdColumnsToOemsTable extends Migration
{
    const TABLE_NAME       = 'oems';
    const TABLE_MODEL_TYPE = 'model_types';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('model_type_id')
                ->nullable()
                ->after('series_id')
                ->constrained('model_types')
                ->nullOnDelete();
        });

        $subQuery = 'SELECT id FROM ' . self::TABLE_MODEL_TYPE . ' WHERE name = ' . self::TABLE_NAME . '.unit_type';
        DB::statement('UPDATE ' . self::TABLE_NAME . ' SET model_type_id = (' . $subQuery . ')');

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('unit_type');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('unit_type', 100)->nullable()->after('unit_image');
        });

        $subQuery = 'SELECT name FROM ' . self::TABLE_MODEL_TYPE . ' WHERE id = ' . self::TABLE_NAME . '.model_type_id';
        DB::statement('UPDATE ' . self::TABLE_NAME . ' SET unit_type = (' . $subQuery . ')');

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['model_type_id']);
            $table->dropColumn('model_type_id');
        });
    }
}
