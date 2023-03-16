<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @method static ActivityFactory factory()
 * @method static static create($attributes = [])
 *
 * @mixin Media
 */
class Activity extends BaseActivity
{
    use HasFactory;

    const ACTION_CREATED          = 'created';
    const ACTION_DELETED          = 'deleted';
    const ACTION_REPLIED          = 'replied';
    const ACTION_SELECTED         = 'selected';
    const ACTION_UPDATED          = 'updated';
    const CART_ITEM_LOG           = 'cart_item_log';
    const ORDER_LOG               = 'order_log';
    const RESOURCE_COMMENT        = 'comment';
    const RESOURCE_ORDER          = 'order';
    const RESOURCE_CART_ITEM      = 'cart_item';
    const RESOURCE_ORDER_ITEM     = 'item_order';
    const RESOURCE_ORDER_DELIVERY = 'order_delivery';
    const RESOURCE_POST           = 'post';
    const RESOURCE_PROFILE        = 'profile';
    const RESOURCE_SOLUTION       = 'solution';
    const TYPE_FORUM              = 'forum';
    const TYPE_ORDER              = 'order';
    const TYPE_PROFILE            = 'profile';
    const TYPE_ALL                = [self::TYPE_FORUM, self::TYPE_ORDER, self::TYPE_PROFILE];
    /* |--- GLOBAL VARIABLES ---| */

    protected $table = 'activity_log';

    /* |--- FUNCTIONS ---| */

    public static function tableName()
    {
        return (new Activity())->table;
    }

    /* |--- RELATIONS ---| */

    public function relatedActivity(): HasMany
    {
        return $this->hasMany(RelatedActivity::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
