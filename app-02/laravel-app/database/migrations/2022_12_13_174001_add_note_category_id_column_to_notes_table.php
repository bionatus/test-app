<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteCategoryIdColumnToNotesTable extends Migration
{
    const TABLE_NAME = 'notes';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->foreignId('note_category_id')
                ->nullable()
                ->after('id')
                ->constrained('note_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropForeign(self::TABLE_NAME.'_note_category_id_foreign');
            $table->dropColumn('note_category_id');
        });
    }
}
