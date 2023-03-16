<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecommendedReplacementsTable extends Migration
{
    const TABLE_NAME = 'recommended_replacements';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('original_part_id')->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('brand');
            $table->string('part_number');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
