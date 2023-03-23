<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemTable extends Migration
{
    const TABLE_NAME = 'cart_item';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('cart_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('replacement_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('generic_part_description')->nullable();
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['cart_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
