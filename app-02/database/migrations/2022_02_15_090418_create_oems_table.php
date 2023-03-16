<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOemsTable extends Migration
{
    const TABLE_NAME = 'oems';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('status', 50)->nullable();
            $table->foreignId('series_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('model', 200);
            $table->text('logo');
            $table->text('unit_image')->nullable();
            $table->string('unit_type', 100)->index()->nullable();
            $table->text('new_system_type')->nullable();
            $table->string('forum_tag', 100)->nullable();
            $table->text('model_description')->nullable();
            $table->text('model_notes')->nullable();
            $table->boolean('show_parts')->nullable();
            $table->text('service_facts')->nullable();
            $table->text('product_data')->nullable();
            $table->text('iom')->nullable();
            $table->text('controls_manuals')->nullable();
            $table->text('bluon_guidelines')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('wiring_diagram')->nullable();
            $table->text('misc')->nullable();
            $table->text('nomenclature')->nullable();
            $table->decimal('tonnage', 10)->nullable();
            $table->integer('total_circuits')->nullable();
            $table->string('dx_chiller', 25)->nullable();
            $table->string('cooling_type', 50)->nullable();
            $table->string('heating_type', 50)->nullable();
            $table->string('seer', 25)->nullable();
            $table->string('eer', 25)->nullable();
            $table->string('refrigerant', 100)->nullable();
            $table->string('original_charge_oz', 100)->nullable();
            $table->string('compressor_brand', 50)->nullable();
            $table->string('compressor_type', 50)->nullable();
            $table->text('compressor_model')->nullable();
            $table->integer('total_compressors')->nullable();
            $table->integer('compressors_per_circuit')->nullable();
            $table->string('compressor_sizes', 50)->nullable();
            $table->string('rla', 100)->nullable();
            $table->text('lra')->nullable();
            $table->string('capacity_staging', 50)->nullable();
            $table->string('lowest_staging', 50)->nullable();
            $table->text('compressor_notes')->nullable();
            $table->string('oil_type', 100)->nullable();
            $table->string('oil_amt_oz', 50)->nullable();
            $table->text('oil_notes')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->integer('devices_per_circuit')->nullable();
            $table->integer('total_devices')->nullable();
            $table->string('device_size', 100)->nullable();
            $table->text('metering_device_notes')->nullable();
            $table->string('fan_type', 50)->nullable();
            $table->string('cfm_range', 50)->nullable();
            $table->text('fan_notes')->nullable();
            $table->string('voltage_phase_hz', 50)->nullable();
            $table->text('standard_controls')->nullable();
            $table->string('optional_controls', 100)->nullable();
            $table->string('conversion_job', 50)->nullable();
            $table->string('warnings', 100)->nullable();
            $table->string('bid_type', 100)->nullable();
            $table->decimal('man_hours', 10, 0)->nullable();
            $table->decimal('charge_lbs', 10, 0)->nullable();
            $table->string('exv', 50)->nullable();
            $table->integer('exv_qty')->nullable();
            $table->string('exv_2', 50)->nullable();
            $table->integer('exv_2_qty')->nullable();
            $table->string('control_panel', 100)->nullable();
            $table->text('inspect')->nullable();
            $table->string('baseline', 100)->nullable();
            $table->string('airflow_waterflow', 200)->nullable();
            $table->string('recover', 100)->nullable();
            $table->string('controls', 100)->nullable();
            $table->string('replace_install', 100)->nullable();
            $table->string('leak_check', 200)->nullable();
            $table->string('evacuate', 100)->nullable();
            $table->string('charge', 200)->nullable();
            $table->string('tune', 200)->nullable();
            $table->string('verify', 100)->nullable();
            $table->string('label', 100)->nullable();
            $table->text('conversion_notes')->nullable();
            $table->string('date_added', 50)->nullable();
            $table->string('qa', 100)->nullable();
            $table->text('qa_qc_comments')->nullable();
            $table->string('last_qc_date', 100)->nullable();
            $table->string('info_source', 50)->nullable();
            $table->string('source', 25)->nullable();
            $table->text('match')->nullable();
            $table->text('syncing_notes')->nullable();
            $table->string('cooling_btuh', 255)->nullable();
            $table->string('heating_btuh', 255)->nullable();
            $table->timestamps();

            $table->index('model');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
