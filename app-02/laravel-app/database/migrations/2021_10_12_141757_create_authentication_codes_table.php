<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthenticationCodesTable extends Migration
{
    const TABLE_NAME        = 'authentication_codes';
    const TYPE_VERIFICATION = 'verification';
    const TYPE_LOGIN        = 'login';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('phone_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 6);
            $table->enum('type', [self::TYPE_VERIFICATION, self::TYPE_LOGIN]);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
