<?php

use App\Models\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateSubstatusesTable extends Migration
{
    const TABLE_NAME                                 = 'substatuses';
    const STATUS_PENDING_REQUESTED                   = 100;
    const STATUS_PENDING_ASSIGNED                    = 110;
    const STATUS_PENDING_APPROVAL_FULFILLED          = 200;
    const STATUS_PENDING_APPROVAL_QUOTE_NEEDED       = 210;
    const STATUS_PENDING_APPROVAL_QUOTE_UPDATED      = 220;
    const STATUS_APPROVED_AWAITING_DELIVERY          = 300;
    const STATUS_APPROVED_READY_FOR_DELIVERY         = 310;
    const STATUS_APPROVED_DELIVERED                  = 320;
    const STATUS_COMPLETED_DONE                      = 400;
    const STATUS_CANCELED_ABORTED                    = 500;
    const STATUS_CANCELED_CANCELED                   = 510;
    const STATUS_CANCELED_DECLINED                   = 520;
    const STATUS_CANCELED_REJECTED                   = 530;
    const STATUS_CANCELED_BLOCKED_USER               = 540;
    const STATUS_CANCELED_DELETED_USER               = 550;
    const STATUS_NAME_PENDING_REQUESTED              = 'requested';
    const STATUS_NAME_PENDING_ASSIGNED               = 'assigned';
    const STATUS_NAME_PENDING_APPROVAL_FULFILLED     = 'fulfilled';
    const STATUS_NAME_PENDING_APPROVAL_QUOTE_NEEDED  = 'quote_needed';
    const STATUS_NAME_PENDING_APPROVAL_QUOTE_UPDATED = 'quote_updated';
    const STATUS_NAME_APPROVED_AWAITING_DELIVERY     = 'awaiting_delivery';
    const STATUS_NAME_APPROVED_READY_FOR_DELIVERY    = 'ready_for_delivery';
    const STATUS_NAME_APPROVED_DELIVERED             = 'delivered';
    const STATUS_NAME_COMPLETED_DONE                 = 'done';
    const STATUS_NAME_CANCELED_ABORTED               = 'aborted';
    const STATUS_NAME_CANCELED_CANCELED              = 'canceled';
    const STATUS_NAME_CANCELED_DECLINED              = 'declined';
    const STATUS_NAME_CANCELED_REJECTED              = 'rejected';
    const STATUS_NAME_CANCELED_BLOCKED_USER          = 'blocked_user';
    const STATUS_NAME_CANCELED_DELETED_USER          = 'deleted_user';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = Carbon::now()->toIso8601String();

        DB::statement("INSERT INTO " . self::TABLE_NAME . " (id, status_id, name, slug, created_at, updated_at) VALUES
                            (" . self::STATUS_PENDING_REQUESTED . "," . Status::STATUS_PENDING . ",'" . self::STATUS_NAME_PENDING_REQUESTED . "', 'pending-requested', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_PENDING_ASSIGNED . "," . Status::STATUS_PENDING . ",'" . self::STATUS_NAME_PENDING_ASSIGNED . "', 'pending-assigned', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_PENDING_APPROVAL_FULFILLED . "," . Status::STATUS_PENDING_APPROVAL . ",'" . self::STATUS_NAME_PENDING_APPROVAL_FULFILLED . "', 'pending-approval-fulfilled', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_PENDING_APPROVAL_QUOTE_NEEDED . "," . Status::STATUS_PENDING_APPROVAL . ",'" . self::STATUS_NAME_PENDING_APPROVAL_QUOTE_NEEDED . "', 'pending-approval-quote-needed', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_PENDING_APPROVAL_QUOTE_UPDATED . "," . Status::STATUS_PENDING_APPROVAL . ",'" . self::STATUS_NAME_PENDING_APPROVAL_QUOTE_UPDATED . "', 'pending-approval-quote-updated', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_APPROVED_AWAITING_DELIVERY . "," . Status::STATUS_APPROVED . ",'" . self::STATUS_NAME_APPROVED_AWAITING_DELIVERY . "', 'approved-awaiting-delivery', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_APPROVED_READY_FOR_DELIVERY . "," . Status::STATUS_APPROVED . ",'" . self::STATUS_NAME_APPROVED_READY_FOR_DELIVERY . "', 'approved-ready-for-delivery', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_APPROVED_DELIVERED . "," . Status::STATUS_APPROVED . ",'" . self::STATUS_NAME_APPROVED_DELIVERED . "', 'approved-delivered', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_COMPLETED_DONE . "," . Status::STATUS_COMPLETED . ",'" . self::STATUS_NAME_COMPLETED_DONE . "', 'completed-done', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_CANCELED_ABORTED . "," . Status::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED_ABORTED . "', 'canceled-aborted', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_CANCELED_CANCELED . "," . Status::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED_CANCELED . "', 'canceled-canceled', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_CANCELED_DECLINED . "," . Status::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED_DECLINED . "', 'canceled-declined', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_CANCELED_REJECTED . "," . Status::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED_REJECTED . "', 'canceled-rejected', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_CANCELED_BLOCKED_USER . "," . Status::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED_BLOCKED_USER . "', 'canceled-blocked-user', '" . $now . "', '" . $now . "'),
                            (" . self::STATUS_CANCELED_DELETED_USER . "," . Status::STATUS_CANCELED . ",'" . self::STATUS_NAME_CANCELED_DELETED_USER . "', 'canceled-deleted-user', '" . $now . "', '" . $now . "')
                            ");
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
