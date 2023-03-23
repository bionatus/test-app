<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateNewStatusesTable extends Migration
{
    const TABLE_NAME                   = 'statuses';
    const STATUS_PENDING               = 100;
    const STATUS_PENDING_APPROVAL      = 200;
    const STATUS_APPROVED              = 300;
    const STATUS_COMPLETED             = 400;
    const STATUS_CANCELED              = 500;
    const STATUS_NAME_PENDING          = 'pending';
    const STATUS_NAME_PENDING_APPROVAL = 'pending_approval';
    const STATUS_NAME_APPROVED         = 'approved';
    const STATUS_NAME_COMPLETED        = 'completed';
    const STATUS_NAME_CANCELED         = 'canceled';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = Carbon::now()->toIso8601String();

        DB::statement("INSERT INTO " . self::TABLE_NAME . " (id, name, slug, created_at, updated_at) VALUES 
                            (" . self::STATUS_PENDING . ",'" . self::STATUS_NAME_PENDING . "', 'pending', '" . $now . "', '" . $now . "'), 
                            (" . self::STATUS_PENDING_APPROVAL . ",'" . self::STATUS_NAME_PENDING_APPROVAL . "', 'pending-approval', '" . $now . "', '" . $now . "'), 
                            (" . self::STATUS_APPROVED . ",'" . self::STATUS_NAME_APPROVED . "', 'approved', '" . $now . "', '" . $now . "'), 
                            (" . self::STATUS_COMPLETED . ",'" . self::STATUS_NAME_COMPLETED . "', 'completed', '" . $now . "', '" . $now . "'), 
                            (" . self::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED . "', 'canceled', '" . $now . "', '" . $now . "')");
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
