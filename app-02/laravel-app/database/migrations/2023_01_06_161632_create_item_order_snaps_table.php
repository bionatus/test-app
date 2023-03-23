<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemOrderSnapsTable extends Migration
{
    const TABLE_NAME = 'item_order_snap';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('order_snap_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('replacement_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->integer('quantity');
            $table->unsignedBigInteger('price')->nullable();
            $table->string('supply_detail', 50)->nullable();
            $table->string('custom_detail')->nullable();
            $table->string('generic_part_description')->nullable();
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
