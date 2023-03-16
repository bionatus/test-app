<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXoxoVouchers extends Migration
{
    const TABLE_NAME = 'xoxo_vouchers';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('code')->unique();
            $table->string('name');
            $table->string('image');
            $table->string('value_denominations');
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
