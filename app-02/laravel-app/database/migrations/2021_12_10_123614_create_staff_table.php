<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    const TABLE_NAME = 'staff';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('type');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('secondary_email')->nullable();
            $table->timestamp('initial_password_set_at')->nullable();
            $table->timestamp('tos_accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
