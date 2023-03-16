<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplyCategoryIdColumnToSuppliesTable extends Migration
{
    const TABLE_NAME = 'supplies';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('supply_category_id')
                ->nullable()
                ->after('id')
                ->constrained('supply_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('type')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(self::TABLE_NAME . '_supply_category_id_foreign');
            $table->dropColumn('supply_category_id');
            $table->dropColumn('type');
        });
    }
}
