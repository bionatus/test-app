<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingSupplierTable extends Migration
{
    const TABLE_NAME = 'setting_supplier';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->type('bigInteger')
                ->unsigned()
                ->constrained('suppliers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('setting_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['supplier_id', 'setting_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
