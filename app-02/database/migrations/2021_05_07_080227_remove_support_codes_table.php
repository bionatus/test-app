<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSupportCodesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('support_codes');
    }

    public function down()
    {
        Schema::create('support_codes', function(Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
        });
    }
}
