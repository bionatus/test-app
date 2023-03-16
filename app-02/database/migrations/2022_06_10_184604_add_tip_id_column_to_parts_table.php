<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipIdColumnToPartsTable extends Migration
{
    const TABLE_NAME     = 'parts';
    const TABLE_PART_TIP = 'part_tip';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('tip_id')->nullable()->after('id')->constrained('tips');
        });

        Schema::dropIfExists(self::TABLE_PART_TIP);
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['tip_id']);
            $table->dropColumn('tip_id');
        });

        Schema::create(self::TABLE_PART_TIP, function(Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tip_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }
}
