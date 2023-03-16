<?php

use App\Models\SupportCall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUuidColumnToSupportCallsTable extends Migration
{
    const TABLE_NAME = 'support_calls';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->nullable()->after('id');
        });

        SupportCall::cursor()->each(function(SupportCall $supportCall) {
            $supportCall->uuid = Str::uuid();
            $supportCall->save();
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->nullable(false)->change();
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->nullable()->after('id');
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
