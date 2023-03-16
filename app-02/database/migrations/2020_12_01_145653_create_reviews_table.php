<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('reviews', function(Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->text('review')->nullable();
            $table->string('video_url')->nullable();
            $table->text('image')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('sale_price', 8, 2)->nullable();
            $table->integer('value')->nullable();
            $table->integer('utility')->nullable();
            $table->integer('score')->nullable();
            $table->string('url_label')->nullable();
            $table->string('url')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
