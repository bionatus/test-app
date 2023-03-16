<?php

namespace Database\Seeders\Settings\Supplier;

use App\Constants\Environments;
use App\Models\Setting;
use App\Models\Supplier;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\SeedsEnvironment;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const SETTINGS = [
        Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL        => [
            'name'          => "New Bluon Member to be Verified",
            'slug'          => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
        Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS          => [
            'name'          => "New Bluon Member to be Verified",
            'slug'          => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
        Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL       => [
            'name'          => "New Messages",
            'slug'          => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => false,
        ],
        Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS         => [
            'name'          => "New Messages",
            'slug'          => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL => [
            'name'          => "New Order Request comes in",
            'slug'          => Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS   => [
            'name'          => "New Order Request comes in",
            'slug'          => Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL    => [
            'name'          => "Quote/Bid is Approved",
            'slug'          => Setting::SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_APPROVED_NOTIFICATION_SMS      => [
            'name'          => "Quote/Bid is Approved",
            'slug'          => Setting::SLUG_ORDER_APPROVED_NOTIFICATION_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL    => [
            'name'          => "Quote/Bid is Rejected",
            'slug'          => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
        ],
        Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS      => [
            'name'          => "Quote/Bid is Rejected",
            'slug'          => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'group'         => Setting::GROUP_NOTIFICATION,
            'applicable_to' => Supplier::MORPH_ALIAS,
            'type'          => Setting::TYPE_BOOLEAN,
            'value'         => true,
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
