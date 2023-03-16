<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyDisplacementNominalPowerWattsAndCrankcaseHeaterCompressorsTable extends Migration
{
    const TABLE_NAME               = 'compressors';
    const COLUMN_NAME_DISPLACEMENT = 'displacement';
    const COLUMN_NAME_NOMINAL      = 'nominal_power_watts';
    const COLUMN_NAME_CRANKCASE    = 'crankcase_heater';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string(self::COLUMN_NAME_DISPLACEMENT, 25)->change();
            $table->string(self::COLUMN_NAME_NOMINAL, 255)->change();
            $table->string(self::COLUMN_NAME_CRANKCASE, 100)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string(self::COLUMN_NAME_DISPLACEMENT, 10)->change();
            $table->string(self::COLUMN_NAME_NOMINAL, 10)->change();
            $table->boolean(self::COLUMN_NAME_CRANKCASE)->nullable()->change();
        });
    }
}
