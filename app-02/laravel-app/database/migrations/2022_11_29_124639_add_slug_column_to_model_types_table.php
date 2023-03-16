<?php

use App\Models\ModelType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugColumnToModelTypesTable extends Migration
{
    const TABLE_NAME = 'model_types';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
        });

        ModelType::whereNull('slug')->each(function(ModelType $modelType) {
            $modelType->generateSlug();
            $modelType->save();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
