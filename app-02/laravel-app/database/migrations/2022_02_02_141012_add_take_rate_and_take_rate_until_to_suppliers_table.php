<?php

use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddTakeRateAndTakeRateUntilToSuppliersTable extends Migration
{
    const TABLE_NAME = 'suppliers';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $defaultTakeRateUntil = Carbon::create(Supplier::DEFAULT_YEAR, Supplier::DEFAULT_MONTH,
                Supplier::DEFAULT_DAY);

            $table->integer('take_rate')
                ->default(Supplier::DEFAULT_TAKE_RATE)
                ->nullable(false)
                ->after('welcome_displayed_at');
            $table->dateTime('take_rate_until')->default($defaultTakeRateUntil)->nullable(false)->after('take_rate');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('take_rate');
            $table->dropColumn('take_rate_until');
        });
    }
}
