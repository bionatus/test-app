<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingStaffTable extends Migration
{
    const TABLE_NAME = 'setting_staff';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('setting_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['staff_id', 'setting_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
