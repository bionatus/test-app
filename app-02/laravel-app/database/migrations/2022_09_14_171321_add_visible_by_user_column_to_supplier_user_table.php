<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibleByUserColumnToSupplierUserTable extends Migration
{
    const TABLE_NAME = 'supplier_user';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->boolean('visible_by_user')->default(true)->after('cash_buyer');
        });
    }
    
    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('visible_by_user');
        });
    }
}
