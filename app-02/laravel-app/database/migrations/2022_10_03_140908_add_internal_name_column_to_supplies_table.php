<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalNameColumnToSuppliesTable extends Migration
{
    const TABLE_NAME = 'supplies';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('internal_name')->nullable()->after('name');
        });

        DB::statement("UPDATE " . self::TABLE_NAME . " SET internal_name  = name");

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('internal_name')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('internal_name');
        });
    }
}
