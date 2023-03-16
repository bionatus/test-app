<?php

use App\Models\ShipmentDeliveryPreference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateShipmentDeliveryPreferencesTable extends Migration
{
    const        TABLE_NAME                = 'shipment_delivery_preferences';
    public const PREFERENCE_NAME_OVERNIGHT = 'Overnight';
    public const PREFERENCE_NAME_PRIORITY  = 'Priority';
    public const PREFERENCE_NAME_STANDARD  = 'Standard';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->timestamps();
        });

        $now = Carbon::now()->toIso8601String();

        DB::statement("INSERT INTO " . self::TABLE_NAME . " (id, slug, created_at, updated_at) VALUES
                        
                            (" . ShipmentDeliveryPreference::PREFERENCE_OVERNIGHT . ",'" . Str::slug(self::PREFERENCE_NAME_OVERNIGHT) . "', '" . $now . "', '" . $now . "'),
                            (" . ShipmentDeliveryPreference::PREFERENCE_PRIORITY . ",'" . Str::slug(self::PREFERENCE_NAME_PRIORITY) . "', '" . $now . "','" . $now . "'),
                            (" . ShipmentDeliveryPreference::PREFERENCE_STANDARD . ",'" . Str::slug(self::PREFERENCE_NAME_STANDARD) . "', '" . $now . "', '" . $now . "')
                            ");
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}

