<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandIdAndModelNumberToSupportCallsTable extends Migration
{
    const TABLE_NAME = 'support_calls';

    public function up()
    {
        Schema::table('support_calls', function(Blueprint $table) {
            $table->foreignId('missing_oem_brand_id')
                ->nullable()
                ->after('oem_id')
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('missing_oem_model_number')->nullable()->after('missing_oem_brand_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_calls', function(Blueprint $table) {
            $table->dropConstrainedForeignId('missing_oem_brand_id');
            $table->dropColumn('missing_oem_model_number');
        });
    }
}
