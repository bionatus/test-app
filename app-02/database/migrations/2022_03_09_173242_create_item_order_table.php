<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemOrderTable extends Migration
{
    const TABLE_NAME     = 'item_order';
    const STATUS_PENDING = 'pending';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('item_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('replacement_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->integer('quantity');
            $table->unsignedBigInteger('price')->nullable();
            $table->string('supply_detail', 50)->nullable();
            $table->string('generic_part_description')->nullable();
            $table->string('status', 20)->default(self::STATUS_PENDING);
            $table->timestamps();

            $table->unique(['item_id', 'order_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
