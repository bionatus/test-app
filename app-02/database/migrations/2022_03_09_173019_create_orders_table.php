<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    const TABLE_NAME       = 'orders';
    const STATUS_PENDING   = 'pending';
    const DEFAULT_DISCOUNT = 0;
    const DEFAULT_TAX      = 0;

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('oem_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 50)->nullable();
            $table->string('working_on_it')->nullable();
            $table->string('status', 25)->default(self::STATUS_PENDING);
            $table->string('status_detail')->nullable();
            $table->string('bid_number', 24)->nullable();
            $table->string('availability', 50)->nullable();
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
