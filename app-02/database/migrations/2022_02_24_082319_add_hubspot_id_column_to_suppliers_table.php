<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHubspotIdColumnToSuppliersTable extends Migration
{
    const TABLE_NAME = 'suppliers';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->unsignedBigInteger('hubspot_id')->unique()->nullable()->after('airtable_id');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('hubspot_id');
        });
    }
}
