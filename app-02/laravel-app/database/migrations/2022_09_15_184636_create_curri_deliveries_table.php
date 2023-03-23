<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriDeliveriesTable extends Migration
{
    const TABLE_NAME   = 'curri_deliveries';
    const VEHICLE_TYPE = 'car';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }

        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('order_deliveries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('origin_address_id')
                ->nullable()
                ->constrained('addresses')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('destination_address_id')
                ->constrained('addresses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('vehicle_type')->default(self::VEHICLE_TYPE);
            $table->string('quote_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
