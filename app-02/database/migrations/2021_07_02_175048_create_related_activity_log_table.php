<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelatedActivityLogTable extends Migration
{
    const TABLE_NAME = 'related_activity_log';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained(Activity::tableName())->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('user_id');
            $table->enum('resource', ['comment', 'post', 'solution']);
            $table->enum('event', ['created', 'deleted', 'replied', 'selected']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
