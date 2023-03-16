<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropStaffIdColumnFromOrdersTable extends Migration
{
    const TABLE_NAME = 'orders';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if ('sqlite' === DB::connection()->getName()) {
                $table->dropColumn('staff_id');
            } else {
                $table->dropConstrainedForeignId('staff_id');
            }
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('staff_id')
                ->after('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }
}
