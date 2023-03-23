<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePubnubChannelsTable extends Migration
{
    const TABLE_NAME = 'pubnub_channels';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('channel');
            $table->text('user_token')->nullable();
            $table->text('supplier_token')->nullable();
            $table->timestamp('user_token_valid_until')->nullable();
            $table->timestamp('supplier_token_valid_until')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'supplier_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
