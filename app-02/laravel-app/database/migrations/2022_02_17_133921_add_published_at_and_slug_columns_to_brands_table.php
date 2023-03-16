<?php

use App\Models\Brand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddPublishedAtAndSlugColumnsToBrandsTable extends Migration
{
    const TABLE_NAME = 'brands';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->timestamp('published_at')->nullable()->after('logo');
        });

        Brand::cursor()->each(function(Brand $brand) {
            $brand->slug         = Str::slug($brand->name);
            $brand->published_at = Carbon::now();
            $brand->saveQuietly();
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('slug')->change();
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('published_at');
            $table->dropColumn('slug');
        });
    }
}
