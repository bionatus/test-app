<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrumentSupportCallCategoryTable extends Migration
{
    const TABLE_NAME = 'instrument_support_call_category';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('support_call_category_id');
            $table->foreign('support_call_category_id', 'instrument_sc_category_sc_category_id_foreign')
                ->references('id')
                ->on('support_call_categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['instrument_id', 'support_call_category_id'],
                'instrument_sc_category_instrument_id_sc_category_id_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
