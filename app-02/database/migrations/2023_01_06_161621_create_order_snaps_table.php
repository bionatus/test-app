<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSnapsTable extends Migration
{
    const TABLE_NAME       = 'order_snaps';
    const DEFAULT_DISCOUNT = 0;
    const DEFAULT_TAX      = 0;

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->type('integer')
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('oem_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('name', 50)->nullable();
            $table->string('working_on_it')->nullable();
            $table->string('status', 25);
            $table->string('bid_number', 24)->nullable();
            $table->unsignedBigInteger('discount')->default(self::DEFAULT_DISCOUNT);
            $table->unsignedBigInteger('tax')->default(self::DEFAULT_TAX);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
