<?php

use App\Models\SupplierUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToSupplierUserTable extends Migration
{
    public function up()
    {
        Schema::table('supplier_user', function(Blueprint $table) {
            $table->string('status')->default(SupplierUser::STATUS_UNCONFIRMED)->nullable(false)->after('user_id');
            $table->string('customer_tier')->nullable()->after('status');
            $table->boolean('cash_buyer')->default(false)->nullable(false)->after('customer_tier');
        });
    }

    public function down()
    {
        Schema::table('supplier_user', function(Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('customer_tier');
            $table->dropColumn('cash_buyer');
        });
    }
}
