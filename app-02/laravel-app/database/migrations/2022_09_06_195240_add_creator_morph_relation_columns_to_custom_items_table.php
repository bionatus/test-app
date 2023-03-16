<?php

use App\Models\ItemOrder;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorMorphRelationColumnsToCustomItemsTable extends Migration
{
    const TABLE_NAME = 'custom_items';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->nullableMorphs('creator');
        });

        if ('mysql' === DB::connection()->getName()) {
            DB::statement("UPDATE " . self::TABLE_NAME . " ci 
                             INNER JOIN " . ItemOrder::tableName() . " io ON io.item_id = ci.id 
                             INNER JOIN " . Order::tableName() . " o ON o.id = io.order_id 
                             SET creator_id = o.user_id, creator_type = 'user';");
        }
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropMorphs('creator');
        });
    }
}
