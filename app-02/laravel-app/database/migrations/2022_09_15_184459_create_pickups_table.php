<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickupsTable extends Migration
{
    const TABLE_NAME = 'pickups';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }

        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('order_deliveries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
