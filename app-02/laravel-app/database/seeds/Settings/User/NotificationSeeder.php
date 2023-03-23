<?php

namespace Database\Seeders\Settings\User;

use App\Constants\Environments;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\SeedsEnvironment;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SETTINGS = [
        Setting::SLUG_BLUON_POINTS_EARNED_IN_APP                 => [
            'name'          => "Bluon Points Earned",
            'slug'          => Setting::SLUG_BLUON_POINTS_EARNED_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_BLUON_POINTS_EARNED_SMS                    => [
            'name'          => "Bluon Points Earned",
            'slug'          => Setting::SLUG_BLUON_POINTS_EARNED_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_DISABLE_FORUM_NOTIFICATION                 => [
            'name'          => "Disable Forum notifications",
            'slug'          => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
        Setting::SLUG_FORUM_NEW_COMMENTS_ON_A_POST               => [
            'name'          => "New comments on a post I've commented on",
            'slug'          => Setting::SLUG_FORUM_NEW_COMMENTS_ON_A_POST,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_FORUM_NEW_POST_WITH_A_TAG_I_FOLLOW         => [
            'name'          => "New Post with a tag i follow",
            'slug'          => Setting::SLUG_FORUM_NEW_POST_WITH_A_TAG_I_FOLLOW,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_FORUM_MY_COMMENT_IS_BEST_ANSWER            => [
            'name'          => "My comment is marked as best answer",
            'slug'          => Setting::SLUG_FORUM_MY_COMMENT_IS_BEST_ANSWER,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED        => [
            'name'          => "A post I've commented on is marked as solved",
            'slug'          => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_FORUM_SOMEONE_COMMENTS_ON_MY_POST          => [
            'name'          => "Someone comments on my post",
            'slug'          => Setting::SLUG_FORUM_SOMEONE_COMMENTS_ON_MY_POST,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_FORUM_SOMEONE_TAGS_YOU_IN_A_COMMENT        => [
            'name'          => "Someone tags you in a comment",
            'slug'          => Setting::SLUG_FORUM_SOMEONE_TAGS_YOU_IN_A_COMMENT,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_NEW_CHAT_MESSAGE_IN_APP                    => [
            'name'          => "New Chat Messages",
            'slug'          => Setting::SLUG_NEW_CHAT_MESSAGE_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_NEW_CHAT_MESSAGE_SMS                       => [
            'name'          => "New Chat Messages",
            'slug'          => Setting::SLUG_NEW_CHAT_MESSAGE_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_IN_APP         => [
            'name'          => "Quote Approved by your Team",
            'slug'          => Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_SMS            => [
            'name'          => "Quote Approved by your Team",
            'slug'          => Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_IN_APP => [
            'name'          => "Approved Quote Received by Supplier",
            'slug'          => Setting::SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_SMS    => [
            'name'          => "Approved Quote Received by Supplier",
            'slug'          => Setting::SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_IS_CANCELED_IN_APP                   => [
            'name'          => "Quote is Canceled",
            'slug'          => Setting::SLUG_ORDER_IS_CANCELED_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_IS_CANCELED_SMS                      => [
            'name'          => "Quote is Canceled",
            'slug'          => Setting::SLUG_ORDER_IS_CANCELED_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_IN_APP         => [
            'name'          => "Quote is Ready for approval",
            'slug'          => Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS            => [
            'name'          => "Quote is Ready for approval",
            'slug'          => Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_PENDING_APPROVAL_IN_APP              => [
            'name'          => "Quote Pending Approval Reminder",
            'slug'          => Setting::SLUG_ORDER_PENDING_APPROVAL_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_PENDING_APPROVAL_SMS                 => [
            'name'          => "Quote Pending Approval Reminder",
            'slug'          => Setting::SLUG_ORDER_PENDING_APPROVAL_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_IN_APP   => [
            'name'          => "Supplier is Working on Your Quote",
            'slug'          => Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_IN_APP,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
        Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_SMS      => [
            'name'          => "Supplier is Working on Your Quote",
            'slug'          => Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => User::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
    ];

    public function run()
    {
        foreach (self::SETTINGS as $slug => $settingData) {
            Setting::updateOrCreate(['slug' => $slug], $settingData);
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
