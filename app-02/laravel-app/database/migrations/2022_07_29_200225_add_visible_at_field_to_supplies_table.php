<?php

use App\Models\Supply;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddVisibleAtFieldToSuppliesTable extends Migration
{
    const TABLE_NAME = 'supplies';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->timestamp('visible_at')->nullable()->after('type');
        });

        Supply::query()->update(['visible_at' => Carbon::now()]);
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('visible_at');
        });
    }
}
