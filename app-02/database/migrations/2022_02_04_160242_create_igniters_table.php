<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIgnitersTable extends Migration
{
    const TABLE_NAME = 'igniters';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('application', 50)->nullable();
            $table->string('gas_type', 10)->nullable();
            $table->string('voltage', 10)->nullable();
            $table->string('terminal_type', 10)->nullable();
            $table->string('mounting', 100)->nullable();
            $table->string('tip_style', 25)->nullable();
            $table->string('ceramic_block', 10)->nullable();
            $table->string('pilot_btu', 10)->nullable();
            $table->string('orifice_diameter', 10)->nullable();
            $table->string('pilot_tube_length', 10)->nullable();
            $table->string('lead_length', 10)->nullable();
            $table->string('sensor_type', 25)->nullable();
            $table->string('steady_current', 25)->nullable();
            $table->string('temp_rating', 10)->nullable();
            $table->string('time_to_temp', 50)->nullable();
            $table->string('amperage', 50)->nullable();
            $table->string('cold_resistance', 50)->nullable();
            $table->string('max_current', 25)->nullable();
            $table->string('compression_fitting_diameter', 25)->nullable();
            $table->string('probe_length', 25)->nullable();
            $table->string('rod_angle', 25)->nullable();
            $table->string('length', 25)->nullable();
            $table->string('height', 25)->nullable();
            $table->string('width', 25)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
