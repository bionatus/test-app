<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePerformanceIndicatorsTable extends Migration
{
    public function up()
    {
        Schema::create('performance_indicators', function(Blueprint $table) {
            $table->increments('id');
            $table->decimal('monthly_recurring_revenue');
            $table->decimal('yearly_recurring_revenue');
            $table->decimal('daily_volume');
            $table->integer('new_users');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::drop('performance_indicators');
    }
}
