<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactorsTable extends Migration
{
    const TABLE_NAME = 'contactors';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('poles', 25)->nullable();
            $table->string('shunts', 25)->nullable();
            $table->string('coil_voltage', 25)->nullable();
            $table->string('operating_voltage', 50)->nullable();
            $table->string('ph', 10)->nullable();
            $table->string('hz', 10)->nullable();
            $table->string('fla', 50)->nullable();
            $table->string('lra', 50)->nullable();
            $table->string('connection_type', 25)->nullable();
            $table->string('termination_type', 100)->nullable();
            $table->string('resistive_amps', 25)->nullable();
            $table->integer('noninductive_amps')->nullable();
            $table->string('auxialliary_contacts', 25)->nullable();
            $table->string('push_to_test_window', 25)->nullable();
            $table->string('contactor_type', 25)->nullable();
            $table->string('height', 25)->nullable();
            $table->string('width', 25)->nullable();
            $table->string('length', 25)->nullable();
            $table->string('coil_type', 25)->nullable();
            $table->string('max_hp', 200)->nullable();
            $table->integer('fuse_clip_size')->nullable();
            $table->string('enclosure_type', 50)->nullable();
            $table->string('temperature_rating', 50)->nullable();
            $table->string('current_setting_range', 50)->nullable();
            $table->string('reset_type', 25)->nullable();
            $table->string('accessories', 100)->nullable();
            $table->string('overload_relays', 100)->nullable();
            $table->string('overload_time', 25)->nullable();
            $table->string('action', 25)->nullable();
            $table->string('rla', 10)->nullable();
            $table->string('application', 100)->nullable();
            $table->string('switch_current', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
