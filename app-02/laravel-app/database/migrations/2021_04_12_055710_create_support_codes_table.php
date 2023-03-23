<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportCodesTable extends Migration
{
    public function up()
    {
        Schema::create('support_codes', function(Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_codes');
    }
}
