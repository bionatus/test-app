<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonesTable extends Migration
{
    const TABLE_NAME = 'phones';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->nullable()
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('country_code', 6)->default(1);
            $table->string('number', 15);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['country_code', 'number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
