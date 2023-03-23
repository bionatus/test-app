<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use App\Models\SupplyCategory;

class AddVisibleAtColumnToSupplyCategoriesTable extends Migration
{
    const TABLE_NAME = 'supply_categories';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->timestamp('visible_at')->nullable()->after('sort');
        });
        SupplyCategory::query()->update(['visible_at' => Carbon::now()]);
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('visible_at');
        });
    }
}
