<?php

use Database\Seeders\ProductionPostsSeeder;
use Database\Seeders\SystemTagsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeriesSystemTable extends Migration
{
    const TABLE_NAME = 'series_system';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('system_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->unique(['series_id']);
        });

        Artisan::call('db:seed', [
            '--class' => ProductionPostsSeeder::class,
        ]);
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
