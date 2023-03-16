<?php

namespace App\Models;

use Database\Factories\OrderSubstatusFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static OrderSubstatusFactory factory()
 *
 * @mixin OrderSubstatus
 */
class OrderSubstatus extends Pivot
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    protected $with    = ['substatus'];
    protected $casts   = [
        'order_id'     => 'integer',
        'substatus_id' => 'integer',
    ];
    protected $touches = ['order'];

    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function substatus(): BelongsTo
    {
        return $this->belongsTo(Substatus::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /* |--- FUNCTIONS ---| */

    public function isWillCall(): bool
    {
        return in_array($this->substatus_id, [
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ]) || ($this->order->orderDelivery?->isPickup() && $this->substatus_id === Substatus::STATUS_APPROVED_DELIVERED);
    }

    public function isPending(): bool
    {
        return Substatus::isSubstatusInStatusPending($this->substatus_id);
    }

    public function isPendingApproval(): bool
    {
        return Substatus::isSubstatusInStatusPendingApproval($this->substatus_id);
    }

    public function isApproved(): bool
    {
        return Substatus::isSubstatusInStatusApproved($this->substatus_id);
    }

    public function isCompleted(): bool
    {
        return Substatus::isSubstatusInStatusCompleted($this->substatus_id);
    }

    public function isCanceled(): bool
    {
        return Substatus::isSubstatusInStatusCanceled($this->substatus_id);
    }

    public function getStatusName(): string
    {
        return $this->substatus->getStatusName();
    }
}
