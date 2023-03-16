<?php

namespace App\Nova\Resources;

use App;
use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Models\Setting;
use App\Models\Staff as StaffModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @mixin StaffModel */
class Staff extends Resource
{
    public static $model               = StaffModel::class;
    public static $title               = 'name';
    public static $search              = [
        'name',
        'email',
        'phone',
    ];
    public static $displayInNavigation = false;

    public static function newModel()
    {
        $model    = static::$model;
        $instance = new $model;

        $instance->type     = StaffModel::TYPE_COUNTER;
        $instance->password = '';

        return $instance;
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable()->hideFromIndex(),
            Text::make('Name')->rules('nullable', 'max:255'),
            Text::make('Email')->rules('nullable', 'max:255', 'bail', 'email:strict', 'ends_with_tld'),
            Text::make('Phone Number', 'phone')->rules('nullable', 'max:255'),
            Boolean::make('SMS Notification', 'sms_notification')->resolveUsing(function() {
                return App::make(GetNotificationSetting::class,
                    ['staff' => $this->model(), 'slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION])->execute();
            })->fillUsing(function(NovaRequest $request) {
            }),
            Boolean::make('Email Notification', 'email_notification')->resolveUsing(function() {
                return App::make(GetNotificationSetting::class,
                    ['staff' => $this->model(), 'slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION])->execute();
            })->fillUsing(function(NovaRequest $request) {
            }),
        ];
    }
}
