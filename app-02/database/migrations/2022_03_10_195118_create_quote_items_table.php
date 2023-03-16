<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuoteItemsTable extends Migration
{
    const TABLE_NAME = 'quote_items';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('part_uuid', 36);
            $table->string('part_number')->index();
            $table->string('part_type');
            $table->string('part_subtype')->nullable();
            $table->string('part_brand')->nullable();
            $table->integer('quantity');
            $table->unsignedBigInteger('price');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
