<?php

namespace App\Http\Resources\LiveApi\V2\Supplier;

use App\Models\Scopes\ByRouteKey;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Staff $resource */
class CounterStaffResource extends JsonResource
{
    private bool $hasEmailNotification;
    private bool $hasSmsNotification;

    public function __construct(Staff $resource)
    {
        parent::__construct($resource);
        $this->getNotificationSettings();
    }

    public function toArray($request)
    {
        return [
            'id'                 => $this->resource->getRouteKey(),
            'name'               => $this->resource->name,
            'email'              => $this->resource->email,
            'phone'              => $this->resource->phone,
            'email_notification' => $this->hasEmailNotification,
            'sms_notification'   => $this->hasSmsNotification,
            'created_at'         => $this->resource->created_at,
        ];
    }

    private function getNotificationSettings()
    {
        $staffNotificationSettings = $this->resource->settingStaffs;

        /** @var Setting $emailNotificationSetting */
        $emailNotificationSetting = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first();
        /** @var Setting $smsNotificationSetting */
        $smsNotificationSetting = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first();

        $this->hasEmailNotification = (bool) $emailNotificationSetting->value;
        $this->hasSmsNotification   = (bool) $smsNotificationSetting->value;

        $staffNotificationSettings->each(function(SettingStaff $settingStaff) {
            if ($settingStaff->setting->getRouteKey() === Setting::SLUG_STAFF_EMAIL_NOTIFICATION) {
                $this->hasEmailNotification = (bool) $settingStaff->value;
            }

            if ($settingStaff->setting->getRouteKey() === Setting::SLUG_STAFF_SMS_NOTIFICATION) {
                $this->hasSmsNotification = (bool) $settingStaff->value;
            }
        });
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'id'                 => ['type' => ['string', 'null']],
                'name'               => ['type' => ['string']],
                'email'              => ['type' => ['string', 'null']],
                'phone'              => ['type' => ['string', 'null']],
                'sms_notification'   => ['type' => ['boolean']],
                'email_notification' => ['type' => ['boolean']],
                'created_at'         => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'name',
                'email',
                'phone',
                'sms_notification',
                'email_notification',
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
