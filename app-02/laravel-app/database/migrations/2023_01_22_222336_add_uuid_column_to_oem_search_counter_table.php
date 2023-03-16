<?php

use App\Models\OemSearchCounter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class AddUuidColumnToOemSearchCounterTable extends Migration
{
    const TABLE_NAME = 'oem_search_counter';
    private Collection $uuids;

    public function up()
    {
        $this->uuids = Collection::make();

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->nullable()->after('id');
            $table->unique('uuid');
        });

        OemSearchCounter::cursor()->each(function(OemSearchCounter $searchCounter) {
            $searchCounter->uuid = $this->uniqueUuid();
            $searchCounter->saveQuietly();
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

    private function uniqueUuid(): string
    {
        do {
            $uuid = Str::uuid()->toString();
        } while ($this->uuids->has($uuid));

        $this->uuids->put($uuid, $uuid);

        return $uuid;
    }
}
