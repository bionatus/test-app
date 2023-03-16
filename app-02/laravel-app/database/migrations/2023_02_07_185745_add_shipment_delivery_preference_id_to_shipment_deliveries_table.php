
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipmentDeliveryPreferenceIdToShipmentDeliveriesTable extends Migration
{
    const TABLE_NAME = 'shipment_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->foreignId('shipment_delivery_preference_id')
                ->nullable()
                ->after('destination_address_id')
                ->constrained('shipment_delivery_preferences')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropForeign(self::TABLE_NAME.'_shipment_delivery_preference_id_foreign');
            $table->dropColumn('shipment_delivery_preference_id');
        });
    }
}
