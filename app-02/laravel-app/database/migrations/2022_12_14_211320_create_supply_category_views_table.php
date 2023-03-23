<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplyCategoryViewsTable extends Migration
{
    const TABLE_NAME = 'supply_category_views';

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
            $table->foreignId('supply_category_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
