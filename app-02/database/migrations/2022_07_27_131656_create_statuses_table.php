<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    const TABLE_NAME     = 'statuses';
    const TABLE_ORDER    = 'orders';
    const STATUS_PENDING = 'pending';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 25);
            $table->string('sub_status', 25)->nullable();
            $table->string('detail')->nullable();
            $table->timestamps();
        });

        if ('sqlite' !== DB::connection()->getName()) {
            DB::statement("INSERT INTO " . self::TABLE_NAME . " (order_id, name, detail, created_at, updated_at) 
                                SELECT id, status, status_detail, NOW(), NOW() FROM " . self::TABLE_ORDER);
        }
    }

    public function down()
    {
        $subquery = "SELECT detail FROM " . self::TABLE_NAME . " s WHERE s.order_id = o.id ORDER BY created_at DESC LIMIT 1";
        DB::statement("UPDATE " . self::TABLE_ORDER . " o SET status_detail=(" . $subquery . ")");

        Schema::dropIfExists(self::TABLE_NAME);
    }
}
