<?php

use App\Models\PartSearchCounter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class AddUuidColumnToPartSearchCounterTable extends Migration
{
    const TABLE_NAME = 'part_search_counter';
    private Collection $uuids;

    public function up()
    {
        $this->uuids = Collection::make();

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->nullable()->after('id');
            $table->unique('uuid');
        });

        PartSearchCounter::cursor()->each(function(PartSearchCounter $searchCounter) {
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
