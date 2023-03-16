<?php

use App\Models\Series;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AddPublishedAtAndUuidColumnsToSeriesTable extends Migration
{
    const TABLE_NAME = 'series';
    private Collection $uuids;

    public function up()
    {
        $this->uuids = Collection::make();

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->char('uuid', 36)->nullable()->after('brand_id');
            $table->timestamp('published_at')->nullable()->after('image');
        });

        Series::cursor()->each(function(Series $series) {
            $series->uuid         = $this->uniqueUuid();
            $series->published_at = Carbon::now();
            $series->saveQuietly();
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('uuid', 36)->change();
            $table->unique('uuid');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('published_at');
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
