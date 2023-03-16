<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDestinationAddressIdInCurriDeliveriesTable extends Migration
{
    const TABLE_NAME = 'curri_deliveries';
    const COLUMN_DESTINATION_ADDRESS = 'destination_address_id';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (DB::connection()->getName() !== 'sqlite'){
                $table->dropForeign([self::COLUMN_DESTINATION_ADDRESS]);
            }
            $table->foreignId(self::COLUMN_DESTINATION_ADDRESS)
                ->change()
                ->nullable()
                ->after('origin_address_id')
                ->constrained('addresses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            if (DB::connection()->getName() !== 'sqlite'){
                $table->dropForeign([self::COLUMN_DESTINATION_ADDRESS]);
            }
            $table->foreignId(self::COLUMN_DESTINATION_ADDRESS)
                ->change()
                ->after('origin_address_id')
                ->constrained('addresses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
}

