<?php

namespace App\Models;

use Database\Factories\SubstatusFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static SubstatusFactory factory()
 *
 * @mixin Substatus
 */
class Substatus extends Model
{
    /* |--- CONSTANTS ---| */
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
    const STATUSES_PENDING                           = [self::STATUS_PENDING_REQUESTED, self::STATUS_PENDING_ASSIGNED];
    const STATUSES_PENDING_APPROVAL                  = [
        self::STATUS_PENDING_APPROVAL_FULFILLED,
        self::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
        self::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
    ];
    const STATUSES_APPROVED                          = [
        self::STATUS_APPROVED_AWAITING_DELIVERY,
        self::STATUS_APPROVED_READY_FOR_DELIVERY,
        self::STATUS_APPROVED_DELIVERED,
    ];
    const STATUSES_COMPLETED                         = [self::STATUS_COMPLETED_DONE];
    const STATUSES_CANCELED                          = [
        self::STATUS_CANCELED_ABORTED,
        self::STATUS_CANCELED_CANCELED,
        self::STATUS_CANCELED_DECLINED,
        self::STATUS_CANCELED_REJECTED,
        self::STATUS_CANCELED_BLOCKED_USER,
        self::STATUS_CANCELED_DELETED_USER,
    ];
    /* |--- GLOBAL VARIABLES ---| */
    protected $with  = ['status'];
    protected $casts = [
        'id'        => 'integer',
        'status_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function getStatusName(): string
    {
        return $this->status->name;
    }

    public function isPending(): bool
    {
        return $this->status->getKey() === Status::STATUS_PENDING;
    }

    public function isPendingApproval(): bool
    {
        return $this->status->getKey() === Status::STATUS_PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status->getKey() === Status::STATUS_APPROVED;
    }

    public function isCompleted(): bool
    {
        return $this->status->getKey() === Status::STATUS_COMPLETED;
    }

    public function isCanceled(): bool
    {
        return $this->status->getKey() === Status::STATUS_CANCELED;
    }

    public static function isSubstatusInStatusPending(int $substatusId): bool
    {
        return self::isSubstatusIn($substatusId, self::STATUSES_PENDING);
    }

    public static function isSubstatusInStatusPendingApproval(int $substatusId): bool
    {
        return self::isSubstatusIn($substatusId, self::STATUSES_PENDING_APPROVAL);
    }

    public static function isSubstatusInStatusApproved(int $substatusId): bool
    {
        return self::isSubstatusIn($substatusId, self::STATUSES_APPROVED);
    }

    public static function isSubstatusInStatusCompleted(int $substatusId): bool
    {
        return self::isSubstatusIn($substatusId, self::STATUSES_COMPLETED);
    }

    public static function isSubstatusInStatusCanceled(int $substatusId): bool
    {
        return self::isSubstatusIn($substatusId, self::STATUSES_CANCELED);
    }

    private static function isSubstatusIn(int $substatusId, array $statuses): bool
    {
        return in_array($substatusId, $statuses);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /* |--- RELATIONS ---| */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function orderSubstatuses(): HasMany
    {
        return $this->hasMany(OrderSubstatus::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
}
