<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsTable extends Migration
{
    const TABLE_NAME = 'parts';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('number')->index()->comment('AKA part_number');
            $table->string('type')->index()->comment('AKA category');
            $table->string('subtype')->nullable()->comment('AKA subcategory');
            $table->string('brand')->nullable()->comment('AKA part_brand');
            $table->timestamp('published_at')->nullable();
            $table->text('image')->nullable();
            $table->string('ingress_protection')->nullable();
            $table->string('certifications')->nullable();
            $table->string('nema_rating')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
