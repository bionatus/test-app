<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static SettingFactory factory()
 *
 * @mixin Setting
 */
class Setting extends Model
{
    use HasSlug;
    use HasFactory;

    const GROUP_AGENT                                     = 'agent';
    const GROUP_NOTIFICATION                              = 'notification';
    const GROUP_VALIDATION                                = 'validation';
    const SLUG_AGENT_AVAILABLE                            = 'agent-available';
    const SLUG_BID_NUMBER_REQUIRED                        = 'bid-number-required';
    const SLUG_BLUON_POINTS_EARNED_IN_APP                 = 'bluon-points-earned-in-app';
    const SLUG_BLUON_POINTS_EARNED_SMS                    = 'bluon-points-earned-sms';
    const SLUG_DISABLE_FORUM_NOTIFICATION                 = 'disable-forum-notifications';
    const SLUG_FORUM_MY_COMMENT_IS_BEST_ANSWER            = 'forum-my-comment-is-best-answer';
    const SLUG_FORUM_NEW_COMMENTS_ON_A_POST               = 'forum-new-comments-on-a-post';
    const SLUG_FORUM_NEW_POST_WITH_A_TAG_I_FOLLOW         = 'forum-new-post-with-a-tag-i-follow';
    const SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED        = 'forum-post-i-comment-on-is-solved';
    const SLUG_FORUM_SOMEONE_COMMENTS_ON_MY_POST          = 'forum-someone-comments-on-my-post';
    const SLUG_FORUM_SOMEONE_TAGS_YOU_IN_A_COMMENT        = 'forum-someone-tags-you-in-a-comment';
    const SLUG_NEW_CHAT_MESSAGE_IN_APP                    = 'order-new-chat-message-in-app';
    const SLUG_NEW_CHAT_MESSAGE_SMS                       = 'order-new-chat-message-sms';
    const SLUG_NEW_MEMBER_NOTIFICATION_EMAIL              = 'new-member-email';
    const SLUG_NEW_MEMBER_NOTIFICATION_SMS                = 'new-member-sms';
    const SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL             = 'new-message-email';
    const SLUG_NEW_MESSAGE_NOTIFICATION_SMS               = 'new-message-sms';
    const SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL       = 'new-order-request-email';
    const SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS         = 'new-order-request-sms';
    const SLUG_ORDER_APPROVED_BY_YOUR_TEAM_IN_APP         = 'order-approved-by-your-team-in-app';
    const SLUG_ORDER_APPROVED_BY_YOUR_TEAM_SMS            = 'order-approved-by-your-team-sms';
    const SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL          = 'order-approved-email';
    const SLUG_ORDER_APPROVED_NOTIFICATION_SMS            = 'order-approved-sms';
    const SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_IN_APP = 'order-approved-received-by-supplier-in-app';
    const SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_SMS    = 'order-approved-received-by-supplier-sms';
    const SLUG_ORDER_IS_CANCELED_IN_APP                   = 'order-is-canceled-in-app';
    const SLUG_ORDER_IS_CANCELED_SMS                      = 'order-is-canceled-sms';
    const SLUG_ORDER_IS_READY_FOR_APPROVAL_IN_APP         = 'order-is-ready-for-approval-in-app';
    const SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS            = 'order-is-ready-for-approval-sms';
    const SLUG_ORDER_PENDING_APPROVAL_IN_APP              = 'order-pending-approval-in-app';
    const SLUG_ORDER_PENDING_APPROVAL_SMS                 = 'order-pending-approval-sms';
    const SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL          = 'order-rejected-email';
    const SLUG_ORDER_REJECTED_NOTIFICATION_SMS            = 'order-rejected-sms';
    const SLUG_STAFF_EMAIL_NOTIFICATION                   = 'staff-email-notification';
    const SLUG_STAFF_SMS_NOTIFICATION                     = 'staff-sms-notification';
    const SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_IN_APP   = 'supplier-is-working-on-your-order-in-app';
    const SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_SMS      = 'supplier-is-working-on-your-order-sms';
    const TYPE_BOOLEAN                                    = 'boolean';
    const TYPE_DOUBLE                                     = 'double';
    const TYPE_INTEGER                                    = 'integer';
    const TYPE_STRING                                     = 'string';
    /* |--- GLOBAL VARIABLES ---| */

    public $timestamps = false;

    /* |--- FUNCTIONS ---| */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function isGroupAgent(): bool
    {
        return self::GROUP_AGENT === $this->group;
    }

    /* |--- RELATIONS ---| */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function settingUsers(): HasMany
    {
        return $this->hasMany(SettingUser::class);
    }

    public function settingSuppliers(): HasMany
    {
        return $this->hasMany(SettingSupplier::class);
    }

    public function settingStaffs(): HasMany
    {
        return $this->hasMany(SettingStaff::class);
    }

    /* |--- ACCESSORS ---| */

    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return boolval($value);
            case self::TYPE_INTEGER:
                return intval($value);
            case self::TYPE_DOUBLE:
                return floatval($value);
            case self::TYPE_STRING:
            default:
                return strval($value);
        }
    }
    /* |--- MUTATORS ---| */
}
