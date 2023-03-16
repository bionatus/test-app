<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXoxoRedemptions extends Migration
{
    const TABLE_NAME = 'xoxo_redemptions';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedInteger('redemption_code');
            $table->unsignedInteger('voucher_code');
            $table->string('name');
            $table->string('image');
            $table->unsignedInteger('value_denomination');
            $table->unsignedInteger('amount_charged');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
