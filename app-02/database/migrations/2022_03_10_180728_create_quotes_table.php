<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotesTable extends Migration
{
    const TABLE_NAME                  = 'quotes';
    const STATUS_AWAITING_FULFILLMENT = 'awaiting_fulfillment';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('user_uuid', 36);
            $table->string('user_email');
            $table->string('user_first_name');
            $table->string('user_last_name');
            $table->string('user_company')->nullable();
            $table->string('supplier_uuid', 36);
            $table->string('supplier_email');
            $table->string('staff_uuid', 36)->nullable();
            $table->string('staff_email')->nullable();
            $table->boolean('delivery')->default(false);
            $table->string('availability')->nullable();
            $table->string('status', 25)->default(self::STATUS_AWAITING_FULFILLMENT);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
