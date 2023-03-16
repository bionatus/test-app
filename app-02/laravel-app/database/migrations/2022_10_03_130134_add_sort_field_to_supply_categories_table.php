<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortFieldToSupplyCategoriesTable extends Migration
{
    const TABLE_NAME = 'supply_categories';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->integer('sort')->nullable()->after('name');
        });

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->index(['sort']);
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropIndex(['sort']);
        });

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn(['sort']);
        });

    }
}
