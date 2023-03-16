<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndexesInSeriesSystemTable extends Migration
{
    const TABLE_NAME = 'series_system';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropUnique(['series_id']);
            $table->unique(['series_id', 'system_id']);
            $table->foreign('series_id')->references('id')->on('series')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(['series_id']);
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('series_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['series_id', 'system_id']);
        });
    }

    public function down()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->downSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropForeign(['system_id']);
            $table->dropUnique(['series_id', 'system_id']);
            $table->unique(['series_id']);
            $table->foreign('series_id')->references('id')->on('series')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('system_id')->references('id')->on('systems')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function downSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(['series_id']);
            $table->dropColumn(['system_id']);
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('series_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('system_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['series_id']);
        });
    }
}
