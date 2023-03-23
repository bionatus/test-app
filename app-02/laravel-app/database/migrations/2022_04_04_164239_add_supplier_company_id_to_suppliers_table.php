<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierCompanyIdToSuppliersTable extends Migration
{
    const TABLE_NAME = 'suppliers';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('supplier_company_id')
                ->nullable()
                ->after('id')
                ->constrained('supplier_companies')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(self::TABLE_NAME . '_supplier_company_id_foreign');
            $table->dropColumn('supplier_company_id');
        });
    }
}
