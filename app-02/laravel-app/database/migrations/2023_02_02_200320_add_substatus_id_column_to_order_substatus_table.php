<?php

use App\Models\Status;
use App\Models\Substatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSubstatusIdColumnToOrderSubstatusTable extends Migration
{
    const TABLE_NAME = 'order_substatus';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('substatus_id')
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        if ('sqlite' !== DB::connection()->getName()) {
            DB::statement("UPDATE order_substatus os
                                 INNER JOIN orders o ON (o.id = os.order_id)
                                 SET substatus_id = 
                                    (CASE WHEN os.name = '" . Status::STATUS_NAME_PENDING . "' AND o.status <> '" . Status::STATUS_NAME_PENDING . "' 
                                        THEN " . Substatus::STATUS_PENDING_REQUESTED . " 
				                        ELSE (CASE WHEN os.name = '" . Status::STATUS_NAME_PENDING . "' AND o.working_on_it IS NULL 
				                            THEN " . Substatus::STATUS_PENDING_REQUESTED . " 
				                            ELSE (CASE WHEN os.name = '" . Status::STATUS_NAME_PENDING . "' AND o.working_on_it IS NOT NULL 
				                            THEN " . Substatus::STATUS_PENDING_ASSIGNED . " ELSE NULL END) 
				                        END)
				                    END)
                                WHERE os.name = '" . Status::STATUS_NAME_PENDING . "';");

            DB::statement("UPDATE order_substatus os
                                 SET substatus_id = " . Substatus::STATUS_PENDING_APPROVAL_FULFILLED . "
                                 WHERE os.name = '" . Status::STATUS_NAME_PENDING_APPROVAL . "';");

            DB::statement("UPDATE order_substatus os
                                 SET substatus_id = " . Substatus::STATUS_APPROVED_READY_FOR_DELIVERY . "
                                 WHERE os.name = '" . Status::STATUS_NAME_APPROVED . "';");

            DB::statement("UPDATE order_substatus os
                                 SET substatus_id = " . Substatus::STATUS_COMPLETED_DONE . "
                                 WHERE os.name = '" . Status::STATUS_NAME_COMPLETED . "';");

            DB::statement("UPDATE order_substatus os
                                 SET substatus_id = 
                                    (CASE WHEN os.sub_status = '" . Substatus::STATUS_NAME_CANCELED_ABORTED . "' OR os.sub_status IS NULL
                                        THEN " . Substatus::STATUS_CANCELED_ABORTED . " 
				                        ELSE (CASE WHEN os.sub_status = '" . Substatus::STATUS_NAME_CANCELED_CANCELED . "'
				                            THEN " . Substatus::STATUS_CANCELED_CANCELED . " 
				                            ELSE (CASE WHEN os.sub_status = '" . Substatus::STATUS_NAME_CANCELED_DECLINED . "' 
				                                THEN " . Substatus::STATUS_CANCELED_DECLINED . " 
				                                ELSE (CASE WHEN os.sub_status = '" . Substatus::STATUS_NAME_CANCELED_REJECTED . "' 
				                                THEN " . Substatus::STATUS_CANCELED_REJECTED . " 
				                                ELSE NULL END) 
				                            END) 
				                        END)
				                    END)
                                WHERE os.name = '" . Status::STATUS_NAME_CANCELED . "';");
        }
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(self::TABLE_NAME . '_substatus_id_foreign');
            $table->dropColumn('substatus_id');
        });
    }
}
